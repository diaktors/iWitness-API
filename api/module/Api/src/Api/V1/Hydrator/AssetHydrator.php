<?php

namespace Api\V1\Hydrator;

use Perpii\Util\ResourceHelper;

class AssetHydrator extends BaseHydrator
{
    /**
     * @return string array
     */
    protected function getDefaultFields()
    {
        return array(
            'id',
            'filename',
            'filesize',
            'mediaType',
            'lat',
            'lng',
            'width',
            'height',
            'processed'
        );
    }

    protected function extractByValue($object)
    {
        $data = parent::extractByValue($object);
        if ($object->getProcessed()) {
            $id = strtolower($data['id']);
            $uriPath = ResourceHelper::getCurrentUri($this->getResource()) . "/asset/{$id}";
            $data['videoUrl'] = $uriPath . '/videourl.mp4';
        }

        return $data;
    }
} 