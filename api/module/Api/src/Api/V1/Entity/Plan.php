<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Perpii\Doctrine\Filter\SoftDeletable;
use Webonyx\Util\BitField;

/**
 * Plan
 * @ORM\Table(name="plan", uniqueConstraints={@ORM\UniqueConstraint(name="key", columns={"key"})})
 * @ORM\Entity(repositoryClass="Api\V1\Repository\PlanRepository")
 */
class Plan implements ResourceInterface, SoftDeletable
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
     * @ORM\Column(name="key", type="string", length=50, nullable=false)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", precision=10, scale=2, nullable=false)
     */
    private $price = '0.00';
    /**
     * @var float
     *
     * @ORM\Column(name="member_price", type="float", scale=2, nullable=false)
     */
    private $member_price = '0.00';


    /**
     * @var integer
     *
     * @ORM\Column(name="length", type="integer", nullable=false)
     */
    private $length;


    /**
     * @var BitField
     *
     * @ORM\Column(name="flags", type="integer", nullable=false)
     */
    private $flags = '0';

    /**
     * @param string $uuid
     * @param $key
     */
    public function __construct($uuid, $key)
    {
        $this->id = $uuid;
        $this->key = $key;
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
     * Get key
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set  key
     *
     * @param string $name
     * @return Plan
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set  key
     *
     * @param string $description
     * @return Plan
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getMemberPrice()
    {
        return $this->member_price;
    }

    /**
     * Set price
     *
     * @param $price
     * @return Coupon
     */
    public function setMemberPrice($price)
    {
        $this->member_price = $price;

        return $this;
    }
	/**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param $price
     * @return Coupon
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get length
     *
     * @return integer
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set length
     *
     * @param $length
     * @return Plan
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get flags
     *
     * @return BitField
     */
    public function getFlags()
    {
        if (is_int($this->flags)) {
            return new BitField($this->flags);
        }
        return $this->flags;
    }

    /**
     * Set flags
     *
     * @param BitField $flags
     * @return User
     */
    public function setFlags($flags)
    {
        if ($flags instanceof BitField) {
            $this->flags = $flags->toInt();
        } else {
            $this->flags = $flags;
        }
        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_PLAN;
    }
}
