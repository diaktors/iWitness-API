<?php
namespace Api\V1\Rest\Prospect;

use Api\V1\Resource\ResourceFactoryTrait;

class ProspectResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $prospectService = $services->get('Api\\V1\\Service\\ProspectService');
        $resource = new ProspectResource($prospectService);
        $resource = $this->initialize($resource, $services);
        return $resource;
    }
}
