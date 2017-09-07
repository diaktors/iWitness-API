<?php

namespace Api\V1\Hydrator;

class DeviceHydrator extends BaseHydrator
{
    protected function getDefaultFields()
    {
        return array(
            'id',
            'token',
            'model'
        );
    }
} 