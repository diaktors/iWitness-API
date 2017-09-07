<?php
namespace Api\V1\Hydrator;

use Webonyx\Util\BitField;

class ContactHydrator extends BaseHydrator
{
    protected function getDefaultFields()
    {
        return array(
            'id',
            'email',
            'phone',
            'phoneAlt',
            'firstName',
            'lastName',
            'flags',
            'ownerId',
            'relationType',
            'created',
            'modified',
            'deleted',
        );
    }

    protected function extractByValue($object)
    {
        $data = parent::extractByValue($object);
        if (isset($data['flags']) &&
            $data['flags'] instanceof BitField
        ) {
            $data['flags'] = $data['flags']->toInt(); //todo: should change to enum
        }
        return $data;
    }
}