<?php

namespace Api\V1\Rest\Device;

use Api\V1\Resource\ResourceFactoryTrait;

class DeviceResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $config = $services->get('config');
        $deviceService = $services->get('Api\\V1\\Service\\DeviceService');

        $resource = new DeviceResource($config, $deviceService);
        $resource = $this->initialize($resource, $services);

        return $resource;
    }
}