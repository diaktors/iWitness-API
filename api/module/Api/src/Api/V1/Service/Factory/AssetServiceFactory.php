<?php

namespace Api\V1\Service\Factory;

use Api\V1\Service\AssetService;
use Api\V1\Service\Config\AssetConfig;
use Aws\S3\S3Client;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AssetServiceFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('Doctrine\\ORM\\EntityManager');
        $entityManager->getFilters()->enable("soft-deletable");
        $config = $serviceLocator->get('config');
        $awsConfig = $config['aws'];

        if ($awsConfig['useS3Storage'] === true) {
            $assetConfig = new AssetConfig($config['assets']['s3']);
            $s3 = S3Client::factory([
                'key'    => $awsConfig['key'],
                'secret' => $awsConfig['secret']
            ]);

            $s3->registerStreamWrapper();
        } else {
            $assetConfig = new AssetConfig($config['assets']['dev']);
        }

        $ffmpeg = $serviceLocator->get('FFMpeg\\FFMpeg');
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');

        return new AssetService($assetConfig, $ffmpeg, $entityManager, $logger);
    }

}