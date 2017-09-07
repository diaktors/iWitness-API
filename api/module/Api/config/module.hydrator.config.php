<?php
return array(
    'factories' => array(
        'Api\V1\Hydrator\UserHydrator' => 'Api\V1\Hydrator\Factory\UserHydratorFactory',
        'Api\V1\Hydrator\SubscriberHydrator' => 'Api\V1\Hydrator\Factory\SubscriberHydratorFactory',
        'Api\V1\Hydrator\ContactHydrator' => 'Api\V1\Hydrator\Factory\ContactHydratorFactory',
        'Api\V1\Hydrator\AdminHydrator' => 'Api\V1\Hydrator\Factory\AdminHydratorFactory',
        'Api\V1\Hydrator\EventHydrator' => 'Api\V1\Hydrator\Factory\EventHydratorFactory',
        'Api\V1\Hydrator\AssetHydrator' => 'Api\V1\Hydrator\Factory\AssetHydratorFactory',
        'Api\V1\Hydrator\MessageHydrator' => 'Api\V1\Hydrator\Factory\MessageHydratorFactory',
        'Api\V1\Hydrator\DeviceHydrator' => 'Api\V1\Hydrator\Factory\DeviceHydratorFactory',
        'Api\V1\Hydrator\CouponHydrator' => 'Api\V1\Hydrator\Factory\CouponHydratorFactory',
        'Api\V1\Hydrator\SubscriptionHydrator' => 'Api\V1\Hydrator\Factory\SubscriptionHydratorFactory',
        'Api\V1\Hydrator\ProspectHydrator' => 'Api\V1\Hydrator\Factory\ProspectHydratorFactory',
        'Api\V1\Hydrator\PlanHydrator' => 'Api\V1\Hydrator\Factory\PlanHydratorFactory',
    )
);
