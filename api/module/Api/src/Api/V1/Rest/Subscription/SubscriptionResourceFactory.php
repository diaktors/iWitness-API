<?php
namespace Api\V1\Rest\Subscription;

use Api\V1\Resource\ResourceFactoryTrait;

class SubscriptionResourceFactory
{
    use ResourceFactoryTrait;

    public function __invoke($services)
    {
        $subscriptionService = $services->get('Api\V1\Service\SubscriptionService');
        $googleInAppService = $services->get('Api\V1\Service\Payment\GoogleInAppService');
        $appleInAppService = $services->get('Api\V1\Service\Payment\AppleInAppService');
        $userService = $services->get('Api\\V1\\Service\\UserService');
        $resource = new SubscriptionResource(
            $subscriptionService,
            $userService,
            $googleInAppService,
            $appleInAppService
        );
        $resource = $this->initialize($resource, $services);
        return $resource;
    }
}