<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Perpii\Doctrine\Filter\SoftDeletable;

/**
 * Setting
 * @ORM\Table(name="email_fallback", uniqueConstraints={@ORM\UniqueConstraint(name="email_id", columns={"data_key"})})
 * @ORM\Entity
 */
class EmailFallback implements ResourceInterface, SoftDeletable
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
     * @ORM\Column(name="email_id", type="integer", nullable=false)
     */
    private $emailId;


    /**
     * @param string $uuid
     * @param $emailId
     */
    public function __construct($uuid, $emailId )
    {
        $this->id = $uuid;
        $this->emailId = $emailId;
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
    public function getEmailId()
    {
        return $this->emailId;
    }


    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_EMAIL_FALLBACK;
    }
}
