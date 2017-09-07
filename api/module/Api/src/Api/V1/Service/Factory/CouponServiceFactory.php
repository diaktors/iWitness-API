<?php


namespace Api\V1\Service\Factory;

use Api\V1\Service\CouponService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CouponServiceFactory implements FactoryInterface
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

        return new CouponService($entityManager, $logger);
    }
}