<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Perpii\Doctrine\Filter\SoftDeletable;

/**
 * Setting
 * @ORM\Table(name="setting", uniqueConstraints={@ORM\UniqueConstraint(name="key", columns={"data_key"})})
 * @ORM\Entity
 */
class Setting implements ResourceInterface, SoftDeletable
{
    use SoftDeleteTrait;
    use AuditTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="uuid", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="data_key", type="string", length=50, nullable=false)
     */
    private $key;

    /**
     * @var data
     *
     * @ORM\Column(name="data_value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * @var type
     *
     * @ORM\Column(name="data_type", type="string", length=20, nullable=false)
     */
    private $type;

    /**
     * @param string $uuid
     * @param $key
     * @param $type
     */
    public function __construct($uuid, $key, $type)
    {
        $this->id = $uuid;
        $this->key = $key;
        $this->type =$type;
        $this->created = time();
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }


    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set  valye
     *
     * @param string $value
     * @return Setting
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_SETTING;
    }
}
