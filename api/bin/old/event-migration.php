#!/usr/bin/php
<?php

require dirname(__FILE__) . '/../Bootstrap.php';

ini_set('display_errors', true);

use FFMpeg\Coordinate\FrameRate;
use FFMpeg\Coordinate\TimeCode;
use Perpii\FFMpeg\Format\Video\X264;

class EventMigration
{
    private static $oldApi = array(
        'event-cache-dir' => '',
        'host' => 'iw-db-01.ce1mskef1ivg.us-east-1.rds.amazonaws.com',
        'port' => '3306',
        'user' => 'admin',
        'password' => '3ightball',
        'dbname' => 'perpcast_api', //Should change this to correct old database

    );

    private static $newApi = array(
        'event-cache-dir' => '/volumes/data/events/cache',
        'host' => 'iw-db-01.ce1mskef1ivg.us-east-1.rds.amazonaws.com',
        'port' => '3306',
        'user' => 'admin',
        'password' => '3ightball',
        'dbname' => 'iwitness_api', //Should change this to correct new database
    );


    /** @var FFMpeg */
    private $ffmpeg = null;

    public function __construct()
    {
        Bootstrap::initializeLogger('logger-main');
        $serviceManager = Bootstrap::getServiceManager();
        $this->ffmpeg = $serviceManager->get('FFMpeg\\FFMpeg');
    }

    /**
     * Start  process
     */
    public function run()
    {
        $this->migrateDatabase();
        //$this->migrateVideos();
    }


    /**
     * migrate Database
     */
    private function migrateDatabase()
    {
        echo "Begin to migrate database" . PHP_EOL;
        $scripts = array(
            'user.sql',
            'coupon.sql',
            'subscription.sql',
            'event.sql',
            'asset.sql',
            'prospect.sql',
            'sender.sql',
            'receipient.sql',
            'contact.sql',
        );

        foreach ($scripts as $script) {
            //replace db prefix
            $sql = file_get_contents(APPLICATION_PATH . '/bin/old/sql/' . $script);
            $sql = str_replace('perpcast_old', self::$oldApi['dbname'], $sql);
            $sql = str_replace('perpcast_api_migrated', self::$newApi['dbname'], $sql);

            echo "Running SQL migrations from $script" . PHP_EOL;

            //execute sql
            $connection = $this->getNewApiConnection();
            $connection->beginTransaction();
            $connection->exec($sql);
            $connection->commit();

            $error = $connection->errorInfo();
            if ($error[2]) {
                echo "ERROR: " . $error[2] . PHP_EOL;
                exit;
            }

        }
        echo "End of migrate database" . PHP_EOL;
    }

    /**
     * migrate Videos
     */
    private function migrateVideos()
    {
        echo "\n  Begin to migrate video";
        $oldEvents = $this->getOldEvents();
        foreach ($oldEvents as $event) {
            echo "\n  Convert event = " . $event['event_uuid'];
            $this->convertToMp4($event);
        }

        echo "\n  End of migrate video";
    }


    /**
     * @param array $event
     */
    public function convertToMp4(array $event)
    {
        try {
            $eventUid = $event['event_uuid'];

            $oldBaseDir = $this->getOldEventCacheDir($eventUid);
            $oldMp4Path = $this->getLocalEventPath($oldBaseDir, $eventUid, 'mpg');
            $oldJpgPath = $this->getLocalEventPath($oldBaseDir, $eventUid, 'jpg');

            if (!file_exists($oldMp4Path)) {
                throw  new \Exception('Event file does not exist: ' . $oldMp4Path);
            }

            $newBaseDir = $this->createNewEventPath($event);
            $mp4FilePath = $this->getLocalEventPath($newBaseDir, $eventUid, 'mp4');
            $thumbnailFilePath = $this->getLocalEventPath($newBaseDir, $eventUid, 'jpg');

            /** @var \FFMpeg\Media\Video $video */
            $video = $this->ffmpeg->open($oldMp4Path);
            $video->filters()->framerate(new FrameRate(25), 250)->synchronize();
            $format = new X264('libfdk_aac');
            $format->setAudioKiloBitrate(128);

            //save h.264 (mp4) video file
            $video->save($format, $mp4FilePath);

            self::strictChmod($mp4FilePath, 0666);

            if (!file_exists($oldJpgPath)) {
                //create new one
                $mp4Video = $this->ffmpeg->open($mp4FilePath);
                $frame = $mp4Video->frame(TimeCode::fromSeconds(2));
                $frame->save($thumbnailFilePath);
                if (file_exists($thumbnailFilePath)) {
                    self::strictChmod($thumbnailFilePath, 0666);
                }
            } else {
                copy($oldJpgPath, $thumbnailFilePath);
            }

            echo "\n converted ".$mp4FilePath;

        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * @return PDOStatement
     */
    private function getOldEvents()
    {
        $dbh = $this->getOldApiConnection();
        $sql = "SELECT * FROM cast_event WHERE processed = 1";
        return $dbh->query($sql);
    }

    /**
     * @param $event
     * @return string
     * @throws Exception
     */
    private function createNewEventPath($event)
    {
        $newEventPath = $this->getNewEventCacheDir($event['event_uuid']);
        if (!file_exists($newEventPath)) {
            echo "\n create event path = ".$newEventPath ."\n";
            mkdir($newEventPath, 0777, true);
        } else {
            if (!is_writable($newEventPath)) {
                throw new \Exception('Could not write to folder ' . $newEventPath);
            }
        }

        return $newEventPath;
    }

    /**
     * @param $cacheDir
     * @param $eventUuid
     * @param $representation
     * @return string
     * @throws Exception
     */
    private function getLocalEventPath($cacheDir, $eventUuid, $representation)
    {
        switch ($representation) {
            case 'preview':
            case 'jpg':
                $path = $cacheDir . '/' . $eventUuid . '.jpg';
                break;
            case 'mpg':
                $path = $cacheDir . '/' . $eventUuid . '.mpg';
                break;
            case 'download':
            case 'view':
            case 'flv':
                $path = $cacheDir . '/' . $eventUuid . '.flv';
                break;
            case 'mp4':
                $path = $cacheDir . '/' . $eventUuid . '.mp4';
                break;
            default:
                throw new Exception("Unknown event representation: '$representation'");
        }

        return $path;
    }

    /**
     * @param array $eventUuid
     * @return string
     */
    private function getOldEventCacheDir( $eventUuid)
    {
        return self::$oldApi['event-cache-dir'] . '/' . $eventUuid[0] . '/' . $eventUuid[1];
    }

    /**
     * @param array $eventUuid
     * @return string
     */
    private function getNewEventCacheDir( $eventUuid)
    {
        return self::$newApi['event-cache-dir'] . '/' . $eventUuid;
    }

    /**
     * @return PDO
     */
    private function  getNewApiConnection()
    {
        return $this->getConnection(self::$newApi);
    }

    /**
     * @return PDO
     */
    private function  getOldApiConnection()
    {
        return $this->getConnection(self::$oldApi);
    }

    /**
     * @param array $config
     * @return PDO
     */
    private function  getConnection(array $config)
    {
        $dbh = new PDO("mysql:host=" . $config['host'] . ";dbname=".$config['dbname'], $config['user'], $config['password']);
        return $dbh;
    }


    /**
     * @param $filename
     * @param $mode
     * @param int $attempts
     * @return bool
     */
    private static function strictChmod($filename, $mode, $attempts = 2)
    {
        if ($attempts <= 0) {
            return false;
        }
        if (false === ($result = @chmod($filename, $mode))) {
            usleep(500000);
            $result = self::strictChmod($filename, $mode, $attempts - 1);
        }
        return $result;
    }
}


$jobQueue = new EventMigration();
$jobQueue->run();


