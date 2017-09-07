<?php

namespace Api\V1\Rest\Message;

use Api\V1\Resource\ResourceFactoryTrait;

class MessageResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $config = $services->get('config');
        $messageService = $services->get('Api\\V1\\Service\\MessageService');
        $userService = $services->get('Api\\V1\\Service\\UserService');
        $deviceService = $services->get('Api\\V1\\Service\\DeviceService');

        $resource = new MessageResource($config, $messageService, $userService, $deviceService);
        $resource = $this->initialize($resource, $services);

        return $resource;
    }
} 