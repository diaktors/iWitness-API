<?php

namespace Api\V1\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserDevice
 *
 * @ORM\Entity
 * @ORM\Table(name="user_device", indexes={@ORM\Index(name="FK_user_device_user", columns={"user_id"}),@ORM\Index(name="FK_user_device_device", columns={"device_id"})})
 */
class UserDevice
{

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="uuid", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Api\V1\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;


    /**
     * @var string
     *
     * @ORM\Column(name="user_id", type="uuid", length=16, nullable=false)
     */
    private $userId;


    /**
     * @var Device
     *
     * @ORM\ManyToOne(targetEntity="Api\V1\Entity\Device")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="device_id", referencedColumnName="id")
     * })
     */
    private $device;


    /**
     * @var string
     *
     * @ORM\Column(name="device_id", type="uuid", length=16, nullable=false)
     */
    private $deviceId;


    public function __construct($uuid = null)
    {
        if ($uuid) {
            $this->id = $uuid;
        }

    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getDeviceId()
    {
        return $this->deviceId;
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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return UserDevice
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get device
     *
     * @return \Api\V1\Entity\Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set device
     *
     * @param \Api\V1\Entity\Device $device
     * @return UserDevice
     */
    public function setDevice(Device $device = null)
    {
        $this->device = $device;
        return $this;
    }
} 