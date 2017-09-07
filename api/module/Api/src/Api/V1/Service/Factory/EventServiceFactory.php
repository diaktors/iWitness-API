<?php

namespace Api\V1\Service\Factory;

use Api\V1\Service\Config\AssetConfig;
use Api\V1\Service\Config\EventConfig;
use Api\V1\Service\EventService;
use Aws\S3\S3Client;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventServiceFactory implements FactoryInterface
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
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
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

        $eventConfig = new EventConfig($config['events']);
        $ffmpeg = $serviceLocator->get('FFMpeg\\FFMpeg');

        return new EventService($eventConfig, $assetConfig, $entityManager, $ffmpeg, $logger);
    }
}