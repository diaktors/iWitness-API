<?php
namespace Api\V1\Hydrator;

use Api\V1\Entity\Admin;
use Api\V1\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Perpii\Util\ResourceHelper;
use Webonyx\Util\BitField;

class UserHydrator extends BaseHydrator
{
    /**
     * @return string array
     */
    protected function   getDefaultFields()
    {
        return array(
            'id',
            'phone',
            'phoneAlt',
            'firstName',
            'lastName',
            'address1',
            'address2',
            'city',
            'state',
            'zip',
            'email',
            'gender',
            'birthDate',
            'heightFeet',
            'heightInches',
            'weight',
            'eyeColor',
            'hairColor',
            'ethnicity',
            'distFeature',
            'timezone',
            'flags',
            'created',
            'modified',
            'deleted',
            'subscriptionStartAt',
            'subscriptionExpireAt',
            'subscriptionId'
        );
    }

    protected function extractByValue($object)
    {
        $data = parent::extractByValue($object);

        if ($object instanceof Admin) {
            $data['type'] = 'Admin';
        } else if ($object instanceof User) {
            $data['type'] = 'User';
        }

        $data['suspended'] = 0;
        if (isset($data['flags']) &&
            $data['flags'] instanceof BitField &&
            $data['flags']->issetBits(User::STATUS_SUSPENDED)
        ) {
            $data['suspended'] = 1;
        }
        unset($data['flags']);

        $resource = $this->getResource();
        $id = strtolower($data['id']);

        $currentUri = ResourceHelper::getCurrentUri($resource);
        if ($object->getPhoto()) {
            $data['photoUrl'] = "{$currentUri}/user/{$id}/photo/default";
        }

        $data['eventsUrl'] = "{$currentUri}/user/{$id}/event";
        return $data;
    }
}