<?php

namespace Api\V1\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserMessage
 *
 * @ORM\Table(name="user_message",
 *  indexes={
 *      @ORM\Index(name="FK_user_message_user", columns={"user_id"}),
 *      @ORM\Index(name="FK_user_message_message", columns={"message_id"})})
 * @ORM\Entity
 */
class UserMessage
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Api\V1\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;
    /**
     * @var Message
     *
     * @ORM\ManyToOne(targetEntity="Api\V1\Entity\Message")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="message_id", referencedColumnName="id")
     * })
     */
    private $message;

    /**
     * @var integer
     *
     * @ORM\Column(name="`read`", type="integer", nullable=false)
     */
    private $read = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="user_id", type="uuid", length=16, nullable=false)
     */
    private $userId;


    /**
     * @var string
     *
     * @ORM\Column(name="message_id", type="uuid", length=16, nullable=false)
     */
    private $messageId;


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
     * @return UserMessage
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get message
     *
     * @return \Api\V1\Entity\Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param \Api\V1\Entity\Message $message
     * @return UserMessage
     */
    public function setMessage(Message $message = null)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get read
     *
     * @return boolean
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Set read
     *
     * @param boolean $read
     * @return UserMessage
     */
    public function setRead($read)
    {
        $this->read = $read;
        return $this;
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
    public function getMessageId()
    {
        return $this->messageId;
    }
} 