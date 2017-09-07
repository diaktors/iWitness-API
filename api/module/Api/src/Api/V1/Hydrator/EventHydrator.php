<?php

namespace Api\V1\Hydrator;

use Api\V1\Entity\User;
use Perpii\Util\ResourceHelper;
use FFMpeg\FFMpeg;
use Perpii\FFMpeg\VideoConcatenation;

class EventHydrator extends BaseHydrator
{
    /**
     * @return string array
     */
    protected function getDefaultFields()
    {
        return array(
            'id',
            'name',
            'initialLat',
            'initialLong',
            'processed',
			'gps',
			'duration',
            'created'
        );
    }

    protected function extractByValue($object)
    {
        $data = parent::extractByValue($object);
        if ($object->getProcessed()) {
            $id = strtolower($data['id']);
            $uriPath = ResourceHelper::getCurrentUri($this->getResource()) . "/event/{$id}";
            $data['imageUrl'] = $uriPath . '/imageurl';
			$data['videoUrl'] = $uriPath . '/videourl.mp4';
			$data['duration'] = $object->getDuration();
			$data['assets'] = $object->getAssetIds();
            $data['gps'] = $object->getAssetGps();
        }

        $extends = $this->getExtendFields();
        if (in_array('User.timezone', $extends)) {
            /** @var User $user */
            $user = $object->getUser();
            if ($user) {
                $data['user']['timezone'] = $user->getTimezone();
            }
        }
        return $data;
    }
}
