<?php

namespace Api\V1\Hydrator\Factory;

use Api\V1\Hydrator\UserHydrator;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Rest\AbstractResourceListener;

class MessageHydratorFactory extends BaseHydratorFactory
{
    function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource = null)
    {
        return new UserHydrator($entityManager, $resource);
    }
}