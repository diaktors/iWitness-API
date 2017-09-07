<?php

namespace Api\V1\Service\Config;

use Api\V1\Entity\Event;
use Api\V1\Service\MediaFileInfo;
use ZF\Rest\Exception\CreationException;

class EventConfig
{

    /** @var  array */
    private $config;

    /*
        'events' => array(
            'cacheDir' => APPLICATION_PATH . '/data/assets/cache',
        ),
     */

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function cacheDir()
    {
        $dir = $this->config['cacheDir'];

        return $dir;
    }

    /**
     * @return string
     */
    public function trashDir()
    {
        $dir = $this->config['trashDir'];

        return $dir;
    }

    /**
     * @return  int
     */
    public function maxAttempted()
    {
        return (int)$this->config['maxAttempted'];
    }

    /**
     * @param $eventId
     * @return string
     */
    public function getEventTrashDir($eventId)
    {
        // Partition cache to prevent FS nightmare when there are too many files:
        $base = $this->trashDir();

        return $base . '/' . $eventId;
    }

    /**
     * @param $eventId
     * @return string
     */

    public function getEventCacheDir($eventId)
    {
        // Partition cache to prevent FS nightmare when there are too many files:
        $base = $this->cacheDir();

        return $base . '/' . $eventId;
    }

    /**
     * @param Event $event
     * @param $representation
     * @param bool $mkdir
     * @return MediaFileInfo
     */
    public function getLocalEventPath(Event $event, $representation, $mkdir = true)
    {
        $fileInfo = $this->getLocalEventFileInfo($event, $representation, $mkdir);

        return $fileInfo->getFilePath();
    }

    /**
     * @param Event $event
     * @param $representation
     * @param bool $mkdir
     * @throws \ZF\Rest\Exception\CreationException
     * @throws \Exception
     * @return MediaFileInfo
     */
    public function getLocalEventFileInfo(Event $event, $representation, $mkdir = true)
    {
        $eventUuid = $event->getId();
        $cacheDir  = $this->getEventCacheDir($eventUuid);

        if ($mkdir && !file_exists($cacheDir) && !@mkdir($cacheDir, 0777, true)) {
            throw new CreationException('Could not create folder ' . $cacheDir, 500);
        }

        //Why do you check write permission in reading file, this cause issue
        /*
        if (!is_writable($cacheDir)) {
            throw new \Exception('Could not write file to ' . $cacheDir, 500);
        }
        */

        $fileInfo = MediaFileInfo::getMediaInfoFor($eventUuid, $representation);
        $fileInfo->setPath($cacheDir);

        return $fileInfo;
    }

    /**
     * @param Event $event
     * @param $representation
     * @param string $baseUrl
     * @return string
     * @throws Exception
     */
    public static function getEventUrl(Event $event, $representation, $baseUrl = '')
    {
        $eventId  = $event->getId();
        $fileInfo = MediaFileInfo::getMediaInfoFor($eventId, $representation);

        return "{$baseUrl}/api/asset/{$representation}-event/" . $fileInfo->getFileName();
    }
}