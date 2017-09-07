<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Api\V1\Resource\ResourceAbstract;
use Zend\Permissions\Acl\Resource\ResourceInterface;


/**
 * Subscription
 *
 * @ORM\Table(name="subscription", indexes={@ORM\Index(name="FK_subscription_user", columns={"user_id"}), @ORM\Index(name="FK_subscription_coupon", columns={"coupon_id"})})
 * @ORM\Entity(repositoryClass="Api\V1\Repository\SubscriptionRepository")
 */
class Subscription extends DoctrineObject implements ResourceInterface
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
     * @ORM\Column(name="original_phone", type="string", length=20, nullable=false)
     */
    private $originalPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="original_phone_model", type="string", length=64, nullable=false)
     */
    private $originalPhoneModel = '';

    /**
     * @var string
     *
     * @ORM\Column(name="purchased_token", type="string", length=255, nullable=true)
     */
    private $purchasedToken = '';

    /**
     * @var string
     *
     * @ORM\Column(name="product_id", type="string", length=50, nullable=true)
     */
    private $productId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="customer_ip", type="string", length=20, nullable=true)
     */
    private $customerIp;

    /**
     * @var string
     *
     * Automated Recurring Billing  id see http://www.authorize.net/support/ARB_guide.pdf
     *
     * @ORM\Column(name="arb_billing_id", type="string", length=60, nullable=true)
     */
    private $arbBillingId;

    /**
     * @var string
     *
     * @ORM\Column(name="plan", type="string", length=20, nullable=false)
     */
    private $plan = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="start_at", type="integer", nullable=false)
     */
    private $startAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="expire_at", type="integer", nullable=false)
     */
    private $expireAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="receipt_id", type="string", nullable=false)
     */
	private $receiptId;
	/**
     * @var integer
     *
     * @ORM\Column(name="originalreceiptid", type="string", nullable=true)
     */
	private $originalreceiptid;
	/**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="string", nullable=true)
     */
	private $userId;

	/**
	 * @var text
	 *
	 * @ORM\Column(name="receipt_data", type="text", nullable = true)
	 */
	private $receiptData;

    /**
     * @var boolean
     *
     * @ORM\Column(name="suspended", type="boolean", nullable=false)
     */
    private $suspended = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive = '0';

    /**
     * @var \Api\V1\Entity\Coupon
     *
     * @ORM\ManyToOne(targetEntity="Api\V1\Entity\Coupon", inversedBy="subscriptions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="coupon_id", referencedColumnName="id")
     * })
     */
    private $coupon;


    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", precision=10, scale=0, nullable=false)
     */
    private $amount = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="payment_gateway",  type="float",  nullable=true)
     */
    private $paymentGateway;


    /**
     * @var \Api\V1\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Api\V1\Entity\User", inversedBy="subscriptions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;


    /**
     * @param string $uuid
     */
    public function __construct($uuid = null)
    {
        if ($uuid) {
            $this->id = $uuid;
        }
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
     * Get originalPhone
     *
     * @return string
     */
    public function getOriginalPhone()
    {
        return $this->originalPhone;
    }

    /**
     * Set originalPhone
     *
     * @param string $originalPhone
     * @return Subscription
     */
    public function setOriginalPhone($originalPhone)
    {
        $this->originalPhone = $originalPhone;

        return $this;
    }
    /**
     * Get purchasedToken
     * @return string
     */
    public function getPurchasedToken()
    {
        return $this->purchasedToken;
    }

    /**
     * Set purchasedToken
     * @param $purchasedToken
     * @return $this
     */
    public function setPurchasedToken($purchasedToken)
    {
        $this->purchasedToken = $purchasedToken;
        return $this;
    }

    /**
     * Get Product Id
     * @return string
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set ProductId
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * Get originalPhoneModel
     *
     * @return string
     */
    public function getOriginalPhoneModel()
    {
        return $this->originalPhoneModel;
    }

    /**
     * Set originalPhoneModel
     *
     * @param string $originalPhoneModel
     * @return Subscription
     */
    public function setOriginalPhoneModel($originalPhoneModel)
    {
        $this->originalPhoneModel = $originalPhoneModel;

        return $this;
    }

    /**
     * Get customerIp
     *
     * @return string
     */
    public function getCustomerIp()
    {
        return $this->customerIp;
    }

    /**
     * Set customerIp
     *
     * @param string $customerIp
     * @return Subscription
     */
    public function setCustomerIp($customerIp)
    {
        $this->customerIp = $customerIp;

        return $this;
    }

    /**
     * Get arbitraryId
     *
     * @return string
     */
    public function getArbBillingId()
    {
        return $this->arbBillingId;
    }

    /**
     * Get receiptId
     * @return string
     */
    public function getReceiptId()
    {
        return $this->receiptId;
    }
	/**
     * Get receiptId
     * @return string
     */
    public function getOriginalReceiptId()
    {
        return $this->originalreceiptid;
    }
    /**
     * Set receiptId
     *
     * @param string $receiptId
     * @return Subscription
     */
    public function setReceiptId($receiptId)
    {
        $this->receiptId = $receiptId;
        return $this;
    }
    /**
     * Set receiptId
     *
     * @param string $receiptId
     * @return Subscription
     */
    public function setOriginalReceiptId($originalreceiptid)
    {
        $this->originalreceiptid = $originalreceiptid;
        return $this;
    }

    /**
     * Get receiptData
     * @return string
     */
    public function getReceiptData()
    {
        return $this->receiptData;
    }

    /**
     * Set receiptData
     *
     * @param string $receiptData
     * @return Subscription
     */
    public function setReceiptData($receiptData)
    {
        $this->receiptData = $receiptData;
        return $this;
	}

    /**
     * Set arbitraryId
     *
     * @param string $arbitraryId
     * @return Subscription
     */
    public function setArbBillingId($arbitraryId)
    {
        $this->arbBillingId = $arbitraryId;

        return $this;
    }

    /**
     * Get plan
     *
     * @return string
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Set plan
     *
     * @param string $plan
     * @return Subscription
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Subscription
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get startAt
     *
     * @return integer
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set startAt
     *
     * @param integer $startAt
     * @return Subscription
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * Get expireAt
     *
     * @return integer
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * Set expireAt
     *
     * @param integer $expireAt
     * @return Subscription
     */
    public function setExpireAt($expireAt)
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    /**
     * Get suspended
     *
     * @return boolean
     */
    public function getSuspended()
    {
        return $this->suspended;
    }

    /**
     * Set suspended
     *
     * @param boolean $suspended
     * @return Subscription
     */
    public function setSuspended($suspended)
    {
        $this->suspended = $suspended;

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
     * @return Subscription
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }
	/**
     * Set originalPhone
     *
     * @param string $originalPhone
     * @return Subscription
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }
	/**
     * Get originalPhone
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }
    /**
     * Get coupon
     *
     * @return \Api\V1\Entity\Coupon
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * Set coupon
     *
     * @param \Api\V1\Entity\Coupon $coupon
     * @return Subscription
     */
    public function setCoupon(\Api\V1\Entity\Coupon $coupon = null)
    {
        $this->coupon = $coupon;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Api\V1\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param \Api\V1\Entity\User $user
     * @return Subscription
     */
    public function setUser(\Api\V1\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }


    /**
     * @return string
     */
    public function getPaymentGateway()
    {
        return $this->paymentGateway;
    }

    /**
     * @param $paymentGateway
     * @return $this
     */
    public function setPaymentGateway($paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;

        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_SUBSCRIPTION;
    }
}
