<?php
/**
 * Created by PhpStorm.
 * User: hung
 * Date: 7/30/14
 * Time: 10:37 PM
 */

namespace Api\V1\Hydrator\Factory;

use Api\V1\Hydrator\PlanHydrator;
use Doctrine\ORM\EntityManager;
use ZF\Rest\AbstractResourceListener;

class PlanHydratorFactory extends BaseHydratorFactory{

    /**
     * Create Hydrator object
     * @param EntityManager $entityManager
     * @param \Api\V1\Hydrator\Factory\BaseResource|\ZF\Rest\AbstractResourceListener $resource
     * @return \Api\V1\Hydrator\BaseHydrator
     */
    protected function getHydrator(EntityManager $entityManager, AbstractResourceListener $resource =null)
    {
        return new PlanHydrator($entityManager, $resource);
    }
}