<?php

namespace Api\V1\Hydrator;

class AdminHydrator extends UserHydrator
{

    protected function extractByValue($object)
    {
        $data = parent::extractByValue($object);
        $data['type'] = 'Admin';
        return $data;
    }

} 