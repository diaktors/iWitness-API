<?php

namespace Api\V1\Hydrator\Factory;

use Api\V1\Hydrator\CouponHydrator;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Rest\AbstractResourceListener;

class CouponHydratorFactory extends BaseHydratorFactory
{
    protected function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource = null)
    {
        return new CouponHydrator($entityManager, $resource);
    }
} 