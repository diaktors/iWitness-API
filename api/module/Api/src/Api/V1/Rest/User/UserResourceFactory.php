<?php
namespace Api\V1\Rest\User;

use Api\V1\Resource\ResourceFactoryTrait;
use Zend\Stdlib\Hydrator\Filter\FilterComposite;

class UserResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $userService = $services->get('Api\\V1\\Service\\UserService');
        $deviceService = $services->get('Api\\V1\\Service\\DeviceService');
        $emailManager = $services->get('Perpii\\Message\\EmailManager');
        $smsManager = $services->get('Perpii\\Message\\SmsManager');

        $resource = new UserResource(
            $userService,
            $emailManager,
            $smsManager,
            $deviceService
        );

        $resource = $this->initialize($resource, $services);
        return $resource;
    }
}
