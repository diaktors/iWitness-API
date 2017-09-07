<?php
namespace Api\V1\Rest\Event;

use Api\V1\Resource\ResourceFactoryTrait;

class EventResourceFactory
{
    use ResourceFactoryTrait;

    /**
     * @param $services
     * @return \Api\V1\Resource\ResourceAbstract|EventResource
     */
    public function __invoke($services)
    {
        $eventService = $services->get('Api\\V1\\Service\\EventService');
        $resource = new EventResource($eventService);
        $resource = $this->initialize($resource, $services);
        return $resource;
    }
}
