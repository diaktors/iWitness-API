<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Perpii\Doctrine\Filter\SoftDeletable;

/**
 * Coupon
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="integer")
 * @ORM\DiscriminatorMap({"1" = "Coupon", "2" = "GiftCard" })
 * @ORM\Table(name="coupon", uniqueConstraints={@ORM\UniqueConstraint(name="code", columns={"code"})})
 * @ORM\Entity
 */
class Coupon implements ResourceInterface
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
     * @ORM\Column(name="code", type="string", length=40, nullable=false)
     */
    private $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="current_usages", type="integer", nullable=false)
     */
    private $currentUsages = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_redemption", type="integer", nullable=false)
     */
    private $maxRedemption = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="redemption_start_date", type="integer", nullable=true)
     */
    private $redemptionStartDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="redemption_end_date", type="integer", nullable=true)
     */
    private $redemptionEndDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="subscription_length", type="integer", nullable=true)
     */
    private $subscriptionLength;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $price = '0';


    /**
     * @var string
     *
     * @ORM\Column(name="code_string", type="string", nullable=true)
     */
    private $codeString;

    /**
     * @var string
     *
     * @ORM\Column(name="plan", type="string", nullable=true)
     */
    private $plan;


    /**
     *
     * @ORM\OneToMany(targetEntity="Api\V1\Entity\Subscription", mappedBy="coupon")
     */
    private $subscriptions;


    /**
     * @param string $uuid
     */
    public function __construct($uuid = null)
    {
        if ($uuid) {
            $this->id = $uuid;
        }
        $this->created = time();
        $this->subscriptions = new ArrayCollection();
    }

    public function getSubscriptions()
    {
        return $this->subscriptions;
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
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Coupon
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }


    /**
     * Get currentUsages
     *
     * @return integer
     */
    public function getCurrentUsages()
    {
        return $this->currentUsages;
    }

    /**
     * Set currentUsages
     *
     * @param integer $currentUsages
     * @return Coupon
     */
    public function setCurrentUsages($currentUsages)
    {
        $this->currentUsages = $currentUsages;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Coupon
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }


    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Coupon
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get maxRedemption
     *
     * @return integer
     */
    public function getMaxRedemption()
    {
        return $this->maxRedemption;
    }

    /**
     * Set maxRedemption
     *
     * @param integer $maxRedemption
     * @return Coupon
     */
    public function setMaxRedemption($maxRedemption)
    {
        $this->maxRedemption = $maxRedemption;

        return $this;
    }

    /**
     * Get redemptionStartDate
     *
     * @return integer
     */
    public function getRedemptionStartDate()
    {
        return $this->redemptionStartDate;
    }

    /**
     * Set redemptionStartDate
     *
     * @param integer $redemptionStartDate
     * @return Coupon
     */
    public function setRedemptionStartDate($redemptionStartDate)
    {
        $this->redemptionStartDate = $redemptionStartDate;

        return $this;
    }

    /**
     * Get redemptionEndDate
     *
     * @return integer
     */
    public function getRedemptionEndDate()
    {
        return $this->redemptionEndDate;
    }

    /**
     * Set redemptionEndDate
     *
     * @param integer $redemptionEndDate
     * @return Coupon
     */
    public function setRedemptionEndDate($redemptionEndDate)
    {
        $this->redemptionEndDate = $redemptionEndDate;

        return $this;
    }

    /**
     * Get subscriptionLength
     *
     * @return integer
     */
    public function getSubscriptionLength()
    {
        return $this->subscriptionLength;
    }

    /**
     * Set subscriptionLength
     *
     * @param integer $subscriptionLength
     * @return Coupon
     */
    public function setSubscriptionLength($subscriptionLength)
    {
        $this->subscriptionLength = $subscriptionLength;

        return $this;
    }


    /**
     * @return string
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * @param string $plan
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;
        return $this;
    }


    /**
     * @return string
     */
    public function getCodeString()
    {
        return $this->codeString;
    }

    /**
     * @param $codeString
     * @return $this
     */
    public function setCodeString($codeString)
    {
        $this->codeString = $codeString;

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
     * Get isFree
     *
     * @return boolean
     */
    public function isFree()
    {
        if (!empty($this->price) && $this->price > 0) {
            return false;
        }
        return true;
    }

    /**
     * Check plan
     * @param $plan
     * @return bool
     */
    public function isValidPlan($plan)
    {
        return ($this->plan == $plan);
    }


    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_COUPON;
    }
}
