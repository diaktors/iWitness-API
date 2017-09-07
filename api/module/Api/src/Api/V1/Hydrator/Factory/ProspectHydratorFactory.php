<?php

namespace Api\V1\Hydrator\Factory;


use Api\V1\Hydrator\ProspectHydrator;
use Doctrine\ORM\EntityManager;
use ZF\Rest\AbstractResourceListener;

class ProspectHydratorFactory extends BaseHydratorFactory
{
    /**
     * Create Hydrator object
     * @param EntityManager $entityManager
     * @param  \Api\V1\Resource\ResourceAbstract |\ZF\Rest\AbstractResourceListener $resource
     * @return \Api\V1\Hydrator\BaseHydrator
     */
    protected function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource = null )
    {
        return new ProspectHydrator($entityManager, $resource);
    }
}