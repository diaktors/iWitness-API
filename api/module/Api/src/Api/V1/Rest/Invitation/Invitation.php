<?php
namespace Api\V1\Rest\Invitation;

use Perpii\Serializer\ArrayModelTrait;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Webonyx\Util\UUID;

class Invitation extends ArraySerializable
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
