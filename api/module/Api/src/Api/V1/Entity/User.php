<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Perpii\Doctrine\Filter\SoftDeletable;

/**
 *
 * @ORM\Entity(repositoryClass="Api\V1\Repository\UserRepository")
 */
class User extends PersonAbstract implements ResourceInterface, SoftDeletable
{
    /**
     * @var string
     *
     * @ORM\Column(name="subscription_id", type="uuid", length=16, nullable=false)
     */
    private $subscriptionId;

    /**
     * @var integer
     *
     * @ORM\Column(name="subscription_start_at", type="integer", nullable=true)
     */
    private $subscriptionStartAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="subscription_expire_at", type="integer", nullable=true)
     */
    private $subscriptionExpireAt;


    /**
     * @var integer
     *
     * @ORM\Column(name="subscription_last_email", type="integer", nullable=true)
     */
    private $subscriptionLastEmail;

    /**
     * @var contacts
     *
     * @ORM\OneToMany(targetEntity="Api\V1\Entity\Contact", mappedBy="user")
     */
    private $contacts;




    /**
     * @var string
     *
     * @ORM\Column(name="secret_key", type="string", length=128, nullable=true)
     */
    private $secretKey;



    /**
     * @param string $uuid
     */
    public function __construct($uuid = null)
    {
        parent::__construct($uuid);

        $this->contacts = new ArrayCollection();

       // $this->created = time();
       // $this->secretKey = sha1(uniqid(md5(srand()), true));

    }

    /**
     * Get subscriptionId
     *
     * @return string
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * Set subscriptionId
     *
     * @param string $subscriptionId
     * @return User
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
    }

    /**
     * Get subscriptionStartAt
     *
     * @return integer
     */
    public function getSubscriptionStartAt()
    {
        return $this->subscriptionStartAt;
    }

    /**
     * Set subscriptionStartAt
     *
     * @param integer $subscriptionStartAt
     * @return User
     */
    public function setSubscriptionStartAt($subscriptionStartAt)
    {
        $this->subscriptionStartAt = $subscriptionStartAt;

        return $this;
    }

    /**
     * Get subscriptionExpireAt
     *
     * @return integer
     */
    public function getSubscriptionExpireAt()
    {
        return $this->subscriptionExpireAt;
    }

    /**
     * Set subscriptionExpireAt
     *
     * @param integer $subscriptionExpireAt
     * @return User
     */
    public function setSubscriptionExpireAt($subscriptionExpireAt)
    {
        $this->subscriptionExpireAt = $subscriptionExpireAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getSubscriptionLastEmail()
    {
        return $this->subscriptionLastEmail;
    }

    /**
     * @param $subscriptionLastEmail
     * @return $this
     */
    public function setSubscriptionLastEmail($subscriptionLastEmail)
    {
        $this->subscriptionLastEmail = $subscriptionLastEmail;
        return $this;
    }


    /**
     * Get contacts
     *
     * @return Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param Contact $contact
     * @return $this
     */
    public function addContacts($contact)
    {
        if ($contact instanceof Contact) {
            $contact->setUser($this);
            if (!$this->contacts->contains($contact)) {
                $this->contacts->add($contact);
            }
        }
        return $this;
    }

    /**
     * @param Contact $contact
     * @return $this
     */
    public function  removeContacts($contact)
    {
        if ($contact instanceof Contact && $this->contacts->contains($contact)) {
            $this->contacts->remove($contact);
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
        return AclAuthorization::RESOURCE_USER;
    }



    /**
     * @return int
     */
    public function getExpireInDay()
    {
        $time = $this->getSubscriptionExpireAt() - time();
        $day = ceil($time / (24 * 60 * 60));
        return $day;
    }

    /**
     * @return bool
     */
    public function hasExpired()
    {
        if ($this->isAdmin() ||
            $this->getSubscriptionExpireAt() == 0 ||
            $this->getSubscriptionExpireAt() >= time()) {
            return false;
        }
        return true;
    }


    /**
     * Get secretKey
     *
     * @return string
     *
    public function getSecretKey()
	{
		//return 1;
        return $this->secretKey;
	}*/
}
