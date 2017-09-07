<?php

namespace Api\V1\Service\Config;

use Api\V1\Entity\Asset;
use Api\V1\Service\MediaFileInfo;
use ZF\Rest\Exception\CreationException;

class AssetConfig
{

    /** @var  array */
    private $config;

    /*
        'assets' => array(
            'baseDir' => APPLICATION_PATH . '/data/assets',
            'trashDir' => APPLICATION_PATH . '/data/assets/trash',
            'cacheDir' => APPLICATION_PATH . '/data/assets/cache',
            'processingQueue' => 'process_assets'
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
    public function baseDir()
    {
        return $this->config['baseDir'];
    }

    /**
     * @return string
     */
    public function trashDir()
    {
        return $this->config['trashDir'];
    }

    /**
     * @return string
     */
    public function cacheDir()
    {
        return $this->config['cacheDir'];
    }

    /**
     * @return string
     */
    public function processingQueue()
    {
        return $this->config['processingQueue'];
    }

    /**
     * @return  int
     */
    public function maxAttempted()
    {
        return (int)$this->config['maxAttempted'];
    }

    /**
     * @return string
     */
    public function audioEncode()
    {
        return $this->config['audioEncode'];
    }

    /**
     * @param $assetId
     * @return string
     */
    public function getOriginalDir($assetId)
    {
        $originalDir = $this->baseDir() . '/' . $assetId;

        return $originalDir;
    }

    /**
     * @param Asset $asset
     * @param string $representation
     * @param bool $mkdir
     * @throws \ZF\Rest\Exception\CreationException
     * @throws \Exception
     * @return MediaFileInfo
     */
    public function getAssetLocalFileInfo(Asset $asset, $representation = null, $mkdir = true)
    {
        //get from asset
        if (!$representation) {
            $representation = MediaFileInfo::getExtensionForMimeType($asset->getMediaType());
        }

        $assetId  = $asset->getId();
        $cacheDir = $this->getCacheDir($assetId);

        if ($mkdir && !file_exists($cacheDir) && !@mkdir($cacheDir, 0777, true)) {
            throw new CreationException('Could not create folder ' . $cacheDir, 500);
        }

		//should only check if file/folder is readable
		//Updated condition
		if (!is_readable($cacheDir)) {
			return false;
            throw new \Exception('Could not read file from ' . $cacheDir, 500);
        }

        $mediaInfo = MediaFileInfo::getMediaInfoFor($assetId, $representation);
        $mediaInfo->setPath($cacheDir);

        return $mediaInfo;
    }

    /**
     * @param Asset $asset
     * @param null $representation
     * @param bool $mkdir
     * @return string
     */
    public function getAssetLocalCachePath(Asset $asset, $representation = null, $mkdir = true)
    {
		$mediaInfo = $this->getAssetLocalFileInfo($asset, $representation, $mkdir);
		//$this->debug("Mediainfo FilePath: ". $mediaInfo->getFilePath());
		//updated condition
        if ($mediaInfo)
			return $mediaInfo->getFilePath();
		else
			return false;
    }

    /**
     * @param string $assetId
     * @return string
     */
    public function getCacheDir($assetId)
    {
        $cacheDir = $this->cacheDir() . '/' . $assetId;

        return $cacheDir;
    }

    /**
     * @param $assetId
     * @return string
     */
    public function getTrashDir($assetId)
    {
        $cacheDir = $this->trashDir() . '/' . $assetId;

        return $cacheDir;
    }


    /**
     * @param Asset $asset
     * @param $representation
     * @param string $baseUrl
     * @return string
     * @throws Exception
     */
    public static function getAssetUrl(Asset $asset, $representation, $baseUrl = '')
    {
        $mediaInfo = MediaFileInfo::getMediaInfoFor($asset->getId(), $representation);

        return "{$baseUrl}/api/asset/{$representation}/" . $mediaInfo->getFileName();
    }

    /**
     * @param Asset $asset
     * @return string
     * @throws Exception
     */
    public static function getExtensionForMimeType(Asset $asset)
    {
        $mimeType = $asset->getMediaType();

        return MediaFileInfo::getExtensionForMimeType($mimeType);
    }
}
