<?php

namespace Api\V1\Service;

use Exception;

class MediaFileInfo
{
    const TYPE_VIEW = 'view';
    const TYPE_PREVIEW = 'preview';
    const TYPE_DOWNLOAD = 'download';
    const TYPE_JPG = 'jpg';
    const TYPE_MPG = 'mpg';
    const TYPE_MOV = 'mov';
    const TYPE_MP4 = 'mp4';
    const TYPE_3GP = '3gp';
    const TYPE_FLV = 'flv';

    /** @var  string */
    private $fileName;

    /** @var  string */
    private $path = null;

    /** @var  string */
    private $mimeType;

    /** @var  string */
    private $extension;

    /**
     * @param string $fileName
     * @param string $mineType
     * @param string $extension
     */
    public function __construct($fileName, $mineType, $extension)
    {
        $this->mimeType = $mineType;
        $this->fileName = $fileName;
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * The folder of file path
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * The folder of file path
     * @throws \Exception
     * @return string
     */
    public function getPath()
    {
        if ($this->path === null) {
            throw new \Exception('Path was not set');
        }
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->path . '/' . $this->fileName;
    }

    /**
     * @param $mimeType
     * @return string
     * @throws Exception
     */
    public static function getExtensionForMimeType($mimeType)
    {
        switch ($mimeType) {
            case 'video/quicktime':
                $ext = 'mov';
                break;
            case 'video/mp4':
                $ext = 'mp4';
                break;
            case 'video/3gpp':
                $ext = '3gp';
                break;
            case 'video/mpeg':
                $ext = 'mpg';
                break;
            case 'video/x-flv':
                $ext = 'flv';
                break;
            default:
                throw new Exception("Video type {$mimeType} is not supported");
        }

        return $ext;
    }

    /**
     * @param string uuid $mediaId
     * @param string $representation
     * @return MediaFileInfo
     * @throws Exception
     */
    public static function getMediaInfoFor($mediaId, $representation)
    {
        /** @var string $ext */
        $ext = '';
        switch ($representation) {
            case self::TYPE_PREVIEW:
            case self::TYPE_JPG:
                $file = $mediaId . '.jpg';
                $contentType = 'image/jpeg';
                $ext = 'jpg';
                break;
            case self::TYPE_MPG:
                $file = $mediaId . '.mpg';
                $contentType = 'video/mpeg';
                $ext = 'mpg';
                break;
            case self::TYPE_MOV:
                $file = $mediaId . '.mov';
                $contentType = 'video/quicktime';
                break;
            case self::TYPE_VIEW:
            case self::TYPE_DOWNLOAD:
            case self::TYPE_MP4:
                $file = $mediaId . '.mp4';
                $contentType = 'video/mp4';
                $ext = 'mp4';
                break;
            case self::TYPE_3GP:
                $file = $mediaId . '.3gp';
                $contentType = 'video/3gpp';
                $ext = '3gp';
                break;
            case self::TYPE_FLV:
                $file = $mediaId . '.flv';
                $contentType = 'video/x-flv';
                $ext = 'flv';
                break;
            default:
                throw new Exception("Unknown asset representation: '$representation'");
        }

        return new MediaFileInfo($file, $contentType, $ext);
    }
}