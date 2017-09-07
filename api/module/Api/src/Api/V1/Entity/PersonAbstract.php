<?php

namespace Api\V1\Entity;

use Doctrine\ORM\Mapping as ORM;
use Perpii\Doctrine\Filter\SoftDeletable;
use Doctrine\Common\Collections\ArrayCollection;
use Webonyx\Util\BitField;

/**
 * User
 * @ORM\Entity(repositoryClass="Api\V1\Repository\PersonRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="integer")
 * @ORM\DiscriminatorMap({"1" = "User", "2" = "Admin",  "4" = "Prospect", "8"="Sender" })
 * @ORM\Table(name="user")
 */
abstract class PersonAbstract implements SoftDeletable
{
    use SoftDeleteTrait;
    use AuditTrait;

    const USER = 1;
    const ADMIN = 2;

    const STATUS_SUSPENDED = 1;

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
     * @var string
     *
     * @ORM\Column(name="address_1", type="string", length=255, nullable=true)
     */
    private $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_2", type="string", length=255, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=50, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=50, nullable=true)
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=50, nullable=true)
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=128, nullable=false)
     */
    private $email;

    /**
     * @var boolean
     *
     * @ORM\Column(name="gender", type="string", length=11, nullable=true)
     */
    private $gender;

    /**
     * @var integer
     *
     * @ORM\Column(name="birth_date", type="integer", nullable=true)
     */
    private $birthDate;

    /**
     * @var float
     *
     * @ORM\Column(name="height_feet", type="float", precision=10, scale=0, nullable=true)
     */
    private $heightFeet;

    /**
     * @var float
     *
     * @ORM\Column(name="height_inches", type="float", precision=10, scale=0, nullable=true)
     */
    private $heightInches;

    /**
     * @var float
     *
     * @ORM\Column(name="weight", type="float", precision=10, scale=0, nullable=true)
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="eye_color", type="string", length=20, nullable=true)
     */
    private $eyeColor;

    /**
     * @var string
     *
     * @ORM\Column(name="hair_color", type="string", length=20, nullable=true)
     */
    private $hairColor;

    /**
     * @var string
     *
     * @ORM\Column(name="ethnicity", type="string", length=100, nullable=true)
     */
    private $ethnicity;

    /**
     * @var string
     *
     * @ORM\Column(name="dist_feature", type="string", length=255, nullable=true)
     */
    private $distFeature;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=120, nullable=true)
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=100, nullable=true)
     */
    private $timezone;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100, nullable=true)
     */
    private $password;


    /**
     * @var BitField
     *
     * @ORM\Column(name="flags", type="integer", nullable=false)
     */
    private $flags = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="secret_key", type="string", length=128, nullable=true)
     */
    private $secretKey;

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

    /**
     * @return ArrayCollection
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $subscriptions
     */
    public function addSubscriptions(ArrayCollection $subscriptions) //Subscription $subscription)
    {
        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $subscription->setUser($this);
            $this->subscriptions->add($subscription);
        }
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $subscriptions
     */
    public function removeSubscriptions(ArrayCollection $subscriptions)
    {
        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $subscription->setUser(null);
            $this->subscriptions->removeElement($subscription);
        }
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address1
     *
     * @param string $address1
     * @return User
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set address2
     *
     * @param string $address2
     * @return User
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return User
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return User
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
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
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get gender
     *
     * @return boolean
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set gender
     *
     * @param int $gender
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return integer
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set birthDate
     *
     * @param integer $birthDate
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get heightFeet
     *
     * @return float
     */
    public function getHeightFeet()
    {
        return $this->heightFeet;
    }

    /**
     * Set heightFeet
     *
     * @param float $heightFeet
     * @return User
     */
    public function setHeightFeet($heightFeet)
    {
        $this->heightFeet = $heightFeet;

        return $this;
    }

    /**
     * Get heightInches
     *
     * @return float
     */
    public function getHeightInches()
    {
        return $this->heightInches;
    }

    /**
     * Set heightInches
     *
     * @param float $heightInches
     * @return User
     */
    public function setHeightInches($heightInches)
    {
        $this->heightInches = $heightInches;

        return $this;
    }

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set weight
     *
     * @param float $weight
     * @return User
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get eyeColor
     *
     * @return string
     */
    public function getEyeColor()
    {
        return $this->eyeColor;
    }

    /**
     * Set eyeColor
     *
     * @param string $eyeColor
     * @return User
     */
    public function setEyeColor($eyeColor)
    {
        $this->eyeColor = $eyeColor;

        return $this;
    }

    /**
     * Get hairColor
     *
     * @return string
     */
    public function getHairColor()
    {
        return $this->hairColor;
    }

    /**
     * Set hairColor
     *
     * @param string $hairColor
     * @return User
     */
    public function setHairColor($hairColor)
    {
        $this->hairColor = $hairColor;

        return $this;
    }

    /**
     * Get ethnicity
     *
     * @return string
     */
    public function getEthnicity()
    {
        return $this->ethnicity;
    }

    /**
     * Set ethnicity
     *
     * @param string $ethnicity
     * @return User
     */
    public function setEthnicity($ethnicity)
    {
        $this->ethnicity = $ethnicity;

        return $this;
    }

    /**
     * Get distFeature
     *
     * @return string
     */
    public function getDistFeature()
    {
        return $this->distFeature;
    }

    /**
     * Set distFeature
     *
     * @param string $distFeature
     * @return User
     */
    public function setDistFeature($distFeature)
    {
        $this->distFeature = $distFeature;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return User
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return User
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return get_called_class();
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
     * Get secretKey
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Set secretKey
     *
     * @param string $secretKey
     * @return User
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @return $this
     */
    public function toggleSuspended()
    {
        $flags = $this->getFlags();
        $flags->toggleBits(self::STATUS_SUSPENDED);
        $this->setFlags($flags);
        return $this;
    }

    /**
     * @return bool
     */
    public function  isSuspended()
    {
        $flags = $this->getFlags();
        return $flags->issetBits(self::STATUS_SUSPENDED);
    }

    /**
     * @return bool
     */
    public function  isAdmin()
    {
        return false;
    }

}
