<?php

namespace Api\V1\Repository;

use Api\V1\Entity\Plan;
use Doctrine\ORM\EntityRepository;

class PlanRepository extends EntityRepository
{
    /**
     * @param $key
     * @throws \Exception
     * @return null | Plan
     */
    public function findPlanByKey($key)
    {
        $plan = $this->findOneBy(array('key' => $key));
        if (!$plan) {
            throw new \Exception(sprintf('Plan "%s" was not support', $key), 406);
        }
        return $plan;
    }
} 