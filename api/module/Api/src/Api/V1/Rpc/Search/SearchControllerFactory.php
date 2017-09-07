<?php

namespace Api\V1\Rpc\Search;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SearchControllerFactory implements FactoryInterface
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
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');

        $authentication = $serviceLocator->get('Api\\V1\\Security\\Authentication\\AuthenticationService');
        $authorization = $serviceLocator->get('Api\\V1\\Security\\Authorization\\AclAuthorization');
        $userService = $serviceLocator->get('Api\\V1\\Service\\UserService');

        return new SearchController(
            $config,
            $userService,
            $authentication,
            $authorization,
            $logger);
    }
}