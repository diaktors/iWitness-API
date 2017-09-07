<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Perpii\Doctrine\Filter\SoftDeletable;

/**
 * @ORM\Entity(repositoryClass="Api\V1\Repository\GiftCardRepository")
 */
class GiftCard extends Coupon implements ResourceInterface
{

    /**
     * @var string
     *
     * @ORM\Column(name="recipient_email", type="string", length=100, nullable=true)
     */
    private $recipientEmail;


    /**
     * @var string
     *
     * @ORM\Column(name="sender_id", type="uuid", length=16, nullable=true)
     */
    private $senderId;


    /**
     * @var string
     *
     * @ORM\Column(name="subscription_id", type="uuid", length=16, nullable=true)
     */
    private $subscriptionId;


    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=100, nullable=true)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_date", type="integer", length=11, nullable=true)
     */
    private $deliveryDate;


    /**
     * @var boolean
     *
     * @ORM\Column(name="is_deliveved", type="boolean", nullable=false)
     */
	private $isDelivered = '0';


    /**
     * @return string
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @param $subscriptionId
     * @return $this
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->recipientEmail;
	}


    /**
     * @param $recipientEmail
     * @return $this
     */
    public function setRecipientEmail($recipientEmail)
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }


    /**
     * @return string
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * @param $senderId
     * @return $this
     */
    public function setSenderId($senderId)
    {
        $this->senderId = $senderId;

        return $this;
    }


    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * Set code
     *
     * @param $deliveryDate
     * @return $this
     */
    public function setDeliveryDate($deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsDelivered($value)
    {
        $this->isDelivered = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDelivered()
    {
        return $this->isDelivered;
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
