<?php

namespace Api\V1\Rest\Plan;

use Api\V1\Resource\ResourceFactoryTrait;

class PlanResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $planService = $services->get('Api\\V1\\Service\\PlanService');
        $resource = new PlanResource($planService);
        $resource = $this->initialize($resource, $services);
        return $resource;
    }
}
