<?php


namespace Api\V1\Hydrator\Factory;

use Api\V1\Hydrator\AdminHydrator;
use Doctrine\ORM\EntityManager;
use Api\V1\Resource\ResourceAbstract;
use ZF\Rest\AbstractResourceListener;

class AdminHydratorFactory extends BaseHydratorFactory
{


    /**
     * Create Hydrator object
     * @param EntityManager $entityManager
     * @param \ZF\Rest\AbstractResourceListener $resource
     * @return \Api\V1\Hydrator\BaseHydrator
     */
    protected function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource = null)
    {
        return new AdminHydrator($entityManager, $resource);
    }
}