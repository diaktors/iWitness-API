<?php
namespace Api\V1\Rest\Invitation;

use Api\V1\Resource\ResourceFactoryTrait;

class InvitationResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $emailManager = $services->get('Perpii\Message\EmailManager');
        $resource = new InvitationResource($emailManager);
        $resource = $this->initialize($resource, $services);
        return $resource;
    }
}