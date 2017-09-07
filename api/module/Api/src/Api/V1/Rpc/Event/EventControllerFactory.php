<?php

namespace Api\V1\Rpc\Event;

use Api\V1\Service\Config\EventConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $controller
     * @return \Api\V1\Rpc\Event\EventController
     */
    public function createService(ServiceLocatorInterface $controller)
    {
        $serviceLocator = $controller->getServiceLocator();

        $config = $serviceLocator->get('Config');
        $sendFileConfig = $config['sendFile'];
        $eventConfig = new EventConfig($config['events']);
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');

        $authentication = $serviceLocator->get('Api\\V1\\Security\\Authentication\\AuthenticationService');
        $authorization = $serviceLocator->get('Api\\V1\\Security\\Authorization\\AclAuthorization');
        $eventService = $serviceLocator->get('Api\\V1\\Service\\EventService');

        return new EventController($sendFileConfig, $eventConfig, $eventService, $authentication, $authorization, $logger);
    }
}