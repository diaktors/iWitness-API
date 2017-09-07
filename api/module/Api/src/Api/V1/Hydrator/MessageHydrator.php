<?php

namespace Api\V1\Hydrator;

class MessageHydrator extends BaseHydrator
{
    /**
     * @return string array
     */
    protected function getDefaultFields()
    {
        return array(
            'id',
            'message'
        );
    }
} 