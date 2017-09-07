<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\ORM\Mapping as ORM;
use Perpii\Doctrine\Filter\SoftDeletable;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Webonyx\Util\BitField;

/**
 * Contact
 *
 * @ORM\Table(name="contact")
 * @ORM\Entity
 */
class Contact implements ResourceInterface, SoftDeletable
{
    use SoftDeleteTrait;
    use AuditTrait;

    const PENDING = 1;
    const ACCEPTED = 2;
    const DECLINED = 4;

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="uuid", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var user
     *
     * @ORM\ManyToOne(targetEntity="Api\V1\Entity\User", inversedBy="contacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var uuid
     *
     * @ORM\Column(name="user_id", type="uuid", nullable=false)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=false)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_alt", type="string", length=20, nullable=true)
     */
    private $phoneAlt;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=100, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=100, nullable=true)
     */
    private $lastName;

    /**
     * @var integer
     *
     * @ORM\Column(name="flags", type="integer", nullable=false)
     */
    private $flags = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="relation_type", type="string", length=40, nullable=true)
     */
    private $relationType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=false)
     */
    private $isPrimary = '0';

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
        if ($uuid) {
            $this->id = $uuid;
        }
        $this->created = time();
        $this->secretKey = sha1(uniqid(md5(srand()), true));
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
     * @return \Api\V1\Entity\Contact
     */
    public function setUser(\Api\V1\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return \Api\V1\Entity\Contact
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return \Api\V1\Entity\Contact
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phoneAlt
     *
     * @return string
     */
    public function getPhoneAlt()
    {
        return $this->phoneAlt;
    }

    /**
     * Set phoneAlt
     *
     * @param string $phoneAlt
     * @return \Api\V1\Entity\Contact
     */
    public function setPhoneAlt($phoneAlt)
    {
        $this->phoneAlt = $phoneAlt;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return \Api\V1\Entity\Contact
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return \Api\V1\Entity\Contact
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

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
     * @return BitField
     */
    public function getStatus()
    {
        return $this->getFlags();
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
     * @param $status
     * @return User
     */
    public function setStatus($status)
    {
        return $this->setFlags($status);
    }


    /**
     * Get isPrimary
     *
     * @return boolean
     */
    public function getIsPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * Set isPrimary
     *
     * @param boolean $isPrimary
     * @return \Api\V1\Entity\Contact
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }

    /**
     * Get relationType
     *
     * @return string
     */
    public function getRelationType()
    {
        return $this->relationType;
    }

    /**
     * Set relationType
     *
     * @param string $relationType
     * @return \Api\V1\Entity\Contact
     */
    public function setRelationType($relationType)
    {
        $this->relationType = $relationType;

        return $this;
    }


    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_CONTACT;
    }

    /**
     * Get secretKey
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }
}