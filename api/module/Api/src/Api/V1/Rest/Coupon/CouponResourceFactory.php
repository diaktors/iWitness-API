<?php
namespace Api\V1\Rest\Coupon;

use Api\V1\Resource\ResourceFactoryTrait;

class CouponResourceFactory
{
    use ResourceFactoryTrait;

    /**
     * @param $services
     * @return \Api\V1\Resource\ResourceAbstract|CouponResource
     */
    public function __invoke($services)
    {
        $couponService = $services->get('Api\\V1\\Service\\CouponService');
        $resource = new CouponResource($couponService);
        $resource = $this->initialize($resource, $services);

        return $resource;
    }
}




