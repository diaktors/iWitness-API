<?php

namespace Api\V1\Hydrator\Factory;

use Api\V1\Hydrator\DeviceHydrator;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Rest\AbstractResourceListener;

class DeviceHydratorFactory extends BaseHydratorFactory
{
    protected function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource = null)
    {
        return new DeviceHydrator($entityManager, $resource);
    }
} 