<?php
namespace Api\V1\Rest\Contact;

use Api\V1\Resource\ResourceFactoryTrait;

class ContactResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $config = $services->get('config');
        $contactService = $services->get('Api\\V1\\Service\\ContactService');
        $emailManager = $services->get('Perpii\\Message\\EmailManager');
        $smsManager = $services->get('Perpii\\Message\\SmsManager');
        $userService = $services->get('Api\\V1\\Service\\UserService');

        $resource = new ContactResource($config, $contactService, $userService, $emailManager, $smsManager);
        $resource = $this->initialize($resource, $services);
        return $resource;
    }
}
