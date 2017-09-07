<?php

namespace Api\V1\Security\Authorization;

use Zend\ServiceManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AclAuthorizationFactory implements \Zend\ServiceManager\FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $roleProvider = $serviceLocator->get('Api\V1\Security\Role\UserRoleProvider');
        $authentication = new AclAuthorization($roleProvider);
        return $authentication;
    }
} 