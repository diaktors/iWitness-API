<?php
namespace Api\V1\Security\Role;

use Zend\Form\Annotation\Hydrator;
use Zend\ServiceManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class UserRoleProviderFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $logger        = $serviceLocator->get('Psr\\Log\\LoggerInterface');
        $entityManager = $serviceLocator->get('Doctrine\\ORM\\EntityManager');
        $roleProvider = new UserRoleProvider($entityManager, $logger);
        return $roleProvider;
    }
} 