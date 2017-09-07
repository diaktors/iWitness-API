<?php

namespace Api\V1\Service\Factory;

use Api\V1\Service\UserService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserServiceFactory implements FactoryInterface
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
        $entityManager->getFilters()->enable("soft-deletable");
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');
        $pushNotification = $serviceLocator->get('Perpii\\Message\\PushNotificationManager');
        return new UserService($config, $entityManager, $pushNotification, $logger);
    }
}