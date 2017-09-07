<?php

namespace Api\V1\Hydrator;


class PlanHydrator extends BaseHydrator
{

    /**
     * @return string array
     */
    protected function   getDefaultFields()
    {
        return array(
            'id',
            'key',
            'name',
            'description',
            'price',
            'member_price',
            'length',
            'created',
            'modified',
            'deleted'
        );
    }
}
