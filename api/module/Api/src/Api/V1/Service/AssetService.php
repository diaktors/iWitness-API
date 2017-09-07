<?php

namespace Api\V1\Service;

use Api\V1\Entity\Asset;
use Api\V1\Service\Config\AssetConfig;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use FFMpeg\Coordinate\FrameRate;
use FFMpeg\Coordinate\TimeCode;
use Perpii\FFMpeg\Format\Video\X264;
use Exception;
use PHPExiftool\Reader;
use PHPExiftool\Exception\EmptyCollectionException;
use FFMpeg\FFMpeg;
use FFMpeg\Filters\Video\RotateFilter;
use Psr\Log\LoggerInterface;
use Webonyx\Util\UUID;
use  Neutron\TemporaryFilesystem\Manager as FsManager;

class AssetService extends ServiceAbstract implements BackgroundProcessInterface
{
    const ENTITY_CLASS = 'Api\V1\Entity\Asset';

    /** @var AssetConfig */
    private $assetConfig = null;

    /**
     * @var \Api\V1\Repository\AssetRepository
     */
    private $assetRepository = null;

    /** @var \Api\V1\Repository\EventRepository */
    private $eventRepository = null;

    /** @var FFMpeg */
    private $ffmpeg = null;

    /**
     * @param AssetConfig $assetConfig
     * @param FFMpeg $ffmpeg
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        AssetConfig $assetConfig,
        FFMpeg $ffmpeg,
        EntityManager $entityManager,
        LoggerInterface $logger)
    {
        parent::__construct($entityManager, $logger);

        $this->assetConfig = $assetConfig;
        $this->ffmpeg = $ffmpeg;

        $this->eventRepository = $this->entityManager->getRepository('Api\V1\Entity\Event');
        $this->assetRepository = $this->entityManager->getRepository('Api\V1\Entity\Asset');
    }

    /**
     * Upload media item into physical location and insert one record into database
     *
     * @param $data
     * @param $event
     * @param $user
     * @throws \Exception
     * @return integer
     */
    public function upload($data, $event, $user)
    {
		$mediaInfo = $data['media'];

		$this->logger->error("Data :-".print_r($mediaInfo, true)."--data--");

        if (empty($mediaInfo)) {	

			throw new Exception("Make sure you upload a media file with the name is media");
        }

        $assetId = UUID::generate();
        $mediaName = $mediaInfo['name'];
        $mediaSize = $mediaInfo['size'];
        $mediaType = $mediaInfo['type'];
		$mediaTmpName = $mediaInfo['tmp_name'];

		$target = $this->assetConfig->getOriginalDir($assetId);
 	    usleep(1000000);
		$this->logger->error("Target Folder: [$target]");

        if (!move_uploaded_file($mediaTmpName, $target)) {
		    $this->logger->error("File not uploaded [$target]");
            throw new Exception("Couldn't move uploaded file to new location [$target]");
        }

        // build up asset data structure
        $assetData = array(
            'filename' => $mediaName,
            'filesize' => $mediaSize,
            'mediaType' => $mediaType,
        );
         if(isset($data['stop']) && $data['stop'] == 1)
			 $assetData['stopped'] = 1;
		 else
		     $assetData['stopped'] = 0;

        // remove file data in media tag
        $data['media'] = null;

        /** @var \Api\V1\Entity\Asset $asset */
        $asset = $this->insertAsset($assetId, $data + $assetData, $event, $user);
        return $asset;
    }

    /**
     * Insert asset data into database
     *
     * @param $assetId
     * @param $data
     * @param $event
     * @param $user
     * @internal param null $eventId
     * @return integer
     */
    public function insertAsset($assetId, $data, $event, $user)
    {
		$user_id = $user->getId();
		//$lastassets = $this->assetRepository->fetchlastAssests($user_id);
		//$lastasset = $lastassets[0];

		if($data['stop'] == 1)
		{
			$data['stopped'] = 1;
		}

		if($data['stop'] == 6)
		{
			//$asset_id = $lastasset->getId();
			//$this->logger->error("testing.......".$asset_id);
			//$lasset = new Asset($asset_id); 
			
			//$lasset = $this->assetRepository->find($asset_id);	
			
			//$lasset->stopped = 1;
			//$this->entityManager->flush();
			$data['stopped'] = 0;     
		}
	    $video_id=1;
		//if (count($lastasset) == 0)
		//	$video_id=1;
		//else
		//	$video_id =  $lastasset->video_id + 1;
		$this->logger->error("Video ID: ". $video_id);

		$data['created'] = time();
        $asset = new Asset($assetId);
        $asset->setEvent($event);
		$asset->setUser($user);

		$asset->uptime = time();
	    $asset->stopped = $data['stopped'];
		$asset->video_id = $video_id;
		$asset->userid_text = $user_id;

        $this->hydrator->hydrate($data, $asset);
        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return $asset;
    }

    /**
     * Processes specified asset.
     *
     * - for videos, it will:
     *   rotate, extract geo coords, convert to mpg and generate thumbnail
     *
     * @param string $assetId
     * @param bool $force
     * @throws Exception
     * @return bool
     */
    public function process($assetId, $force = false)
    {
        /** @var Asset $asset */
        $asset = $this->assetRepository->find($assetId);

        if (!$asset) {
            throw new Exception("Asset '$assetId' not found in DB");
        }
        if ($asset->getProcessed() && !$force) {
            throw new Exception("Asset '$assetId' is already processed");
        }
        //$this->debug("Media type: " . $asset->getMediaType());
        if (in_array($asset->getMediaType(), array('video/quicktime', 'video/mp4', 'video/3gpp'))) {
            $this->processVideo($asset);
        }

        $event = $asset->getEvent();
        $event->newAssetProcessed();

        //save data into database
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param Asset $asset
     * @throws \Exception
     */
    private function processVideo(Asset $asset)
	{
		 
        $assetId = $asset->getId();
        $representation = MediaFileInfo::getExtensionForMimeType($asset->getMediaType());

        //create temporary file
        $fs = FsManager::create();
        $tempFilePath = $fs->createTemporaryFile('asset_', null, $representation);
        if (false === ($tmpFileHandle = fopen($tempFilePath, 'w'))) {
            throw new Exception('Could not create temporary file ' . $tempFilePath);
        }

        $originalFilePath = $this->assetConfig->getOriginalDir($assetId);
        if (false === ($originalFileHandle = @fopen($originalFilePath, 'r'))) {
            throw new Exception("Couldn't find original asset '$originalFilePath'");
        }
        //copy file
		stream_copy_to_stream($originalFileHandle, $tmpFileHandle); // better than copy() for s3
        fclose($tmpFileHandle);
        fclose($originalFileHandle);

        if (!file_exists($tempFilePath)) {
            throw new Exception("Could not copy original asset into cache folder");
        }

        /** @var \FFMpeg\Media\Video $video */
        $video = $this->ffmpeg->open($tempFilePath);
        $fileMeta = $this->getMetaData($tempFilePath);

        if ($fileMeta !== false) {

            $asset->setMeta($fileMeta->getMetaInJson());

            //height
            if (!$asset->getHeight() && $fileMeta->getHeight()) {
                $asset->setHeight($fileMeta->getHeight());
            }
            //width
            if (!$asset->getWidth() && $fileMeta->getWidth()) {
                $asset->setWidth($fileMeta->getWidth());
            }
            //latitude
            if (!$asset->getLat() && $fileMeta->getLat()) {
                $asset->setLat($fileMeta->getLat());
            }
            //longitude
            if (!$asset->getLng() && $fileMeta->getLng()) {
                $asset->setLng($fileMeta->getLng());
            }

            //rotate video if required
            $transpose = $fileMeta->getTranspose();
            if (null !== $transpose) {
                if ($transpose === 1) {
                    $video->filters()->rotate(RotateFilter::ROTATE_90);
                } elseif ($transpose === 2) {
                    $video->filters()->rotate(RotateFilter::ROTATE_270);
                }
                $asset = $asset->reverseWithHeight();
            }

            $video->filters()->framerate(new FrameRate(25), 250)->synchronize();

            $audioEncode = $this->assetConfig->audioEncode();
            if ($audioEncode) {
                $format = new X264($audioEncode);
            } else {
                $format = new X264();
            }

            //log progress
            /*
            $format->on('progress', function ($video, $format, $percentage) {
                echo "$percentage % transcoded";
            });
            */

            $format->setAudioKiloBitrate(128);
            $mp4FilePath = $this->assetConfig->getAssetLocalCachePath($asset, 'mp4');
            //save h.264 (mp4) video file
            $video->save($format, $mp4FilePath);

            //save thumbnail jpeg image
            $thumbnailFilePath = $this->assetConfig->getAssetLocalCachePath($asset, 'jpg');
            $mp4Video = $this->ffmpeg->open($mp4FilePath);
            $frame = $mp4Video->frame(TimeCode::fromSeconds(2));
            $frame->save($thumbnailFilePath);

            // set permissions
            self::strictChmod($mp4FilePath, 0666);

            if (file_exists($thumbnailFilePath)) {
                self::strictChmod($thumbnailFilePath, 0666);
            }

            $fs->clean();
        }
    }

    /**
     * Extracts exif metadata from video, using exiftool
     *
     * @see http://www.sno.phy.queensu.ca/~phil/exiftool/
     * @param string $file Path to video file
     * @return MediaMeta | bool
     */
    private function getMetaData($file)
    {
        try {
            $reader = Reader::create($this->logger);
            $metaData = $reader->files($file)->first();
            return new MediaMeta($metaData->getMetadatas()->toArray());
        } catch (EmptyCollectionException $ex) {
            $this->logger->error('Got empty output from exiftool');
            return false;
        }
    }

    /**
     * @param $max
     * @return array
     */
    public function fetchForProcessing($max)
    {
        return $this->assetRepository->fetchForProcessing($max);
    }

    /**
     * @param $id int
     * @param null|string $message
     * @return mixed
     */
    public function markProcessingError($id, $message = null)
    {
        /** @var \Api\V1\Entity\Asset $asset */
        $asset = $this->assetRepository->find($id);

        if (($asset) && !$asset->getProcessed()) {
            $asset->increaseAttempted();
            $asset->addLog($message);
            if ($asset->getAttempted() > $this->assetConfig->maxAttempted()) {
                $asset->setFlags(Asset::FAILURE);
                $asset->setProcessed(1);
            }
            $this->entityManager->flush();
        }
    }

    /**
     * @param $id
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @return mixed
     */
    public function markProcessingSuccess($id)
    {
        /** @var \Api\V1\Entity\Asset $asset */
        $asset = $this->assetRepository->find($id);

        if (!$asset) {
            throw new EntityNotFoundException('Could not found Asset id ' . $id);
        }

        $asset->setFlags(Asset::SUCCESS);
        $asset->setProcessed(true);
        $this->entityManager->flush();
    }

    /**
     * Performs several attempts to set mode of the file, sleeping 0.5 second
     * between attempts (for some reason sometimes several attempts are required)
     *
     * @param string $filename
     * @param int $mode
     * @param int $attempts
     * @return boolean
     */
    public static function strictChmod($filename, $mode, $attempts = 2)
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
