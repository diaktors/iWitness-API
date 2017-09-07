<?php

namespace Api\V1\Hydrator\Factory;

use Api\V1\Hydrator\EventHydrator;
use Doctrine\ORM\EntityManager;
use ZF\Rest\AbstractResourceListener;

class EventHydratorFactory extends BaseHydratorFactory
{
    /**
     * Create Hydrator object
     * @param EntityManager $entityManager
     * @param  \Api\V1\Resource\ResourceAbstract |\ZF\Rest\AbstractResourceListener $resource
     * @return \Api\V1\Hydrator\BaseHydrator
     */
    protected function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource = null)
    {
        return new EventHydrator($entityManager, $resource);
    }
}