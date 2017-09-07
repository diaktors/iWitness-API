<?php
namespace Api\V1\Hydrator;

class CouponHydrator extends BaseHydrator
{
    protected function getDefaultFields()
    {
        return array(
            'id',
            'code',
            'isFree',
            'maxUsages',
            'currentUsages',
            'isActive',
            'price',
            'name',
            'maxRedemption',
            'redemptionStartDate',
            'redemptionEndDate',
            'subscriptionLength',
            'created',
            'modified',
            'deleted',
            'codeString',
            'plan'
        );
    }
}