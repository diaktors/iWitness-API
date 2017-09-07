<?php

namespace Api\V1\Hydrator\Factory;

use Api\V1\Hydrator\AssetHydrator;
use Doctrine\ORM\EntityManager;
use ZF\Rest\AbstractResourceListener;

class AssetHydratorFactory extends BaseHydratorFactory
{

    /**
     * Create Hydrator object
     * @param EntityManager $entityManager
     * @param ResourceAbstract |\ZF\Rest\AbstractResourceListener $resource
     * @return \Api\V1\Hydrator\BaseHydrator
     */
    protected function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource = null)
    {
        return new AssetHydrator($entityManager, $resource);
    }
}