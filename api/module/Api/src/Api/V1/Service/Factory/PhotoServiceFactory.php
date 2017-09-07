<?php

namespace Api\V1\Service\Factory;

use Api\V1\Service\PhotoService;
use Aws\S3\S3Client;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZF\Rest\AbstractResourceListener;

class PhotoServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $serviceLocator->get('Doctrine\\ORM\\EntityManager');
        $entityManager->getFilters()->enable("soft-deletable");

        $config      = $serviceLocator->get('config');

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
        $awsConfig = $config['aws'];

        if ($awsConfig['useS3Storage'] === true) {
            $photoConfig = $config['photos']['s3'];
            $s3 = S3Client::factory([
                'key'    => $awsConfig['key'],
                'secret' => $awsConfig['secret']
            ]);

            $s3->registerStreamWrapper();
        } else {
            $photoConfig = $config['photos']['dev'];
        }

        return new PhotoService($entityManager, $logger, $photoConfig);
    }
}