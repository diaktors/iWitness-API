<?php


namespace Api\V1\Service\Factory;

use Api\V1\Service\GiftCardService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GiftCardServiceFactory implements FactoryInterface
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
        $logger = $serviceLocator->get('Psr\\Log\\LoggerInterface');

        return new GiftCardService($entityManager, $logger);
    }
}