<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Perpii\Doctrine\Filter\SoftDeletable;

/**
 * @ORM\Entity(repositoryClass="Api\V1\Repository\ProspectRepository")
 */
class Prospect extends PersonAbstract  implements ResourceInterface, SoftDeletable
{

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=200, nullable=true)
     */
    private $platform;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=40, nullable=true)
     */
    private $ipAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="user_agent", type="string", length=250, nullable=true)
     */
    private $userAgent;

    /**
     * Get platform
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set platform
     *
     * @param string $platform
     * @return User
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     * @return User
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set userAgent
     *
     * @param string $userAgent
     * @return User
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }


    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_PROSPECT;
    }
}