<?php
namespace Api\V1\Rest\Emergency;

use Perpii\Serializer\ArrayModelTrait;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Webonyx\Util\UUID;

class Emergency extends ArraySerializable
{
    use ArrayModelTrait;

    /**
     * @param null $uuid
     */
    public function __construct($uuid = null)
    {
        $this->id = $uuid ? : UUID::generate();
    }
}