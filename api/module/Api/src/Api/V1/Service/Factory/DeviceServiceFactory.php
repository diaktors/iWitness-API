<?php

namespace Api\V1\Service\Factory;

use Api\V1\Service\DeviceService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DeviceServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $entityManager = $serviceLocator->get('Doctrine\\ORM\\EntityManager');
        $pushNotification = $serviceLocator->get('Perpii\\Message\\PushNotificationManager');
        $entityManager->getFilters()->enable("soft-deletable");
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
        return new DeviceService($config, $entityManager, $pushNotification, $logger);
    }
}