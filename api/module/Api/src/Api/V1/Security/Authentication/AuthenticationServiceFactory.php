<?php

namespace Api\V1\Security\Authentication;

use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationServiceFactory implements \Zend\ServiceManager\FactoryInterface
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
        return new AuthenticationService($entityManager);
    }
}