<?php

namespace Api\V1\Hydrator;

class SubscriptionHydrator extends BaseHydrator
{
    protected function getDefaultFields()
    {
        return array(
			'userId',
            'id',
            'originalPhone',
            'originalPhoneModel',
            'customerIp',
            'arbBillingId',
            'plan',
            'startAt',
            'expireAt',
            'receiptId',
            'originalreceiptid',
            'suspended',
			'isActive'
        );
    }
}
