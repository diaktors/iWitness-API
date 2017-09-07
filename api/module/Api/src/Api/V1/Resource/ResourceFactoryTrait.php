<?php

namespace Api\V1\Resource;

trait ResourceFactoryTrait
{

    /**
     * @param ResourceAbstract $resource
     * @param $services
     * @return ResourceAbstract
     */
    public function initialize(ResourceAbstract &$resource, &$services)
    {
        $authentication = $services->get('Api\\V1\\Security\\Authentication\\AuthenticationService');
        $authorization = $services->get('Api\\V1\\Security\\Authorization\\AclAuthorization');
        $logger = $services->get('Psr\\Log\\LoggerInterface');

        $resource->setLogger($logger);
        $resource->setAuthentication($authentication);
        $resource->setAuthorization($authorization);

        return $resource;
    }
} 