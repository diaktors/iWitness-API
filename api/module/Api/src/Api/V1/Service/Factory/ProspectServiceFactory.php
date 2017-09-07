<?php

namespace Api\V1\Service\Factory;

use Api\V1\Service\ProspectService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProspectServiceFactory implements FactoryInterface
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
        return new ProspectService($config, $entityManager, $logger);
    }
} 