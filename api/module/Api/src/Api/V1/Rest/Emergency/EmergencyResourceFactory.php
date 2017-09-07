<?php
namespace Api\V1\Rest\Emergency;
use Api\V1\Resource\ResourceFactoryTrait;

class EmergencyResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $eventService = $services->get('Api\\V1\\Service\\EventService');
        $emergencyService = $services->get('Api\\V1\\Service\\EmergencyService');
        $resource =  new EmergencyResource(
            $emergencyService,
            $eventService
        );

        $resource = $this->initialize($resource,$services);
        return $resource;
    }
}
