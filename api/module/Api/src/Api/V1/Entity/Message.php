<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Message
 *
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="Api\V1\Repository\MessageRepository")
 */
class Message implements ResourceInterface
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
     * @ORM\Column(name="message", type="string", length=255, nullable=true)
     */
    private $message;

    /**
     * @var integer
     *
     * @ORM\Column(name="flags", type="integer", nullable=false)
     */
    private $flags = '0';

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Api\V1\Entity\UserMessage", mappedBy="user")
     */
    private $userMessages;

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
     * Get text message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set text message
     *
     * @param string $message
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get flags
     *
     * @return integer
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set flags
     *
     * @param integer $flags
     * @return Message
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
        return $this;
    }

    /**
     * Get user messages
     *
     * @return Collection
     */
    public function getUserMessages()
    {
        return $this->userMessages;
    }

    /**
     * @param UserMessage $userMessage
     * @return $this
     */
    public function addUserMessages(UserMessage $userMessage)
    {
        if ($userMessage) {
            $userMessage->setMessage($this);
            if (!$this->userMessages->contains($userMessage)) {
                $this->userMessages->add($userMessage);
            }
        }
        return $this;
    }

    /**
     * @param UserMessage $userMessage
     * @return $this
     */
    public function  removeContacts(UserMessage $userMessage)
    {
        if ($userMessage && $this->userMessages->contains($userMessage)) {
            $this->userMessages->remove($userMessage);
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
        return AclAuthorization::RESOURCE_DEVICE;
    }
}