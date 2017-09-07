<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Asset
 *
 * @ORM\Table(name="asset", indexes={@ORM\Index(name="FK_asset_user", columns={"user_id"}), @ORM\Index(name="FK_asset_event", columns={"event_id"})})
 * @ORM\Entity(repositoryClass="Api\V1\Repository\AssetRepository")
 */
class Asset implements ResourceInterface
{
    use SoftDeleteTrait;
    use AuditTrait;

    const NOT_PROCESS = 1;
    const FAILURE = 2;
    const SUCCESS = 4;
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
     * @ORM\Column(name="filename", type="string", length=255, nullable=true)
     */
    private $filename;
    /**
     * @var integer
     *
     * @ORM\Column(name="filesize", type="integer", nullable=true)
     */
    private $filesize;
    /**
     * @var string
     *
     * @ORM\Column(name="media_type", type="string", length=200, nullable=true)
     */
    private $mediaType;
    /**
     * @var boolean
     *
     * @ORM\Column(name="processed", type="boolean", nullable=false)
     */
    private $processed = '0';
    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", precision=10, scale=6, nullable=true)
     */
    private $lat;
    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float", precision=10, scale=6, nullable=true)
     */
    private $lng;
    /**
     * @var integer
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;
    /**
     * @var integer
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;
    /**
     * @var string
     *
     * @ORM\Column(name="meta", type="blob", nullable=true)
     */
    private $meta;

    /**
     * @var integer
     *
     * @ORM\Column(name="flags", type="integer", nullable=false)
     */
	private $flags = '0';

	/**
	 * * @var integer
	 * *
	 * * @ORM\Column(name="stopped", type="integer", nullable=false)
	 * */
	public $stopped;

	/**
	 * * @var integer
	 * *
	 * * @ORM\Column(name="video_id", type="integer", nullable=false)
	 * */
	public $video_id;

	/**
	 * * @var string
	 * *
	 * * @ORM\Column(name="userid_text", type="string", length=100,  nullable=false)
	 * */
	public $userid_text;

	/**
	 * * @var integer
	 * *
	 * * @ORM\Column(name="uptime", type="integer", nullable=false)
	 * */
	public $uptime;

    /**
     * @var integer
     *
     * @ORM\Column(name="attempted", type="integer", nullable = true)
     */
    private $attempted;
    /**
     * @var integer
     *
     * @ORM\Column(name="log", type="string", nullable = true)
     */
    private $log;

    /**
     * @var \Api\V1\Entity\Event
     *
     * @ORM\ManyToOne(targetEntity="Api\V1\Entity\Event", inversedBy="assets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    private $event;

    /**
     * @var \Api\V1\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Api\V1\Entity\User")
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
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Asset
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filesize
     *
     * @return integer
     */
    public function getFileSize()
    {
        return $this->filesize;
    }

    /**
     * Set filesize
     *
     * @param integer $filesize
     * @return Asset
     */
    public function setFileSize($filesize)
    {
        $this->filesize = $filesize;

        return $this;
    }

    /**
     * Get mediaType
     *
     * @return string
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }

    /**
     * Set mediaType
     *
     * @param string $mediaType
     * @return Asset
     */
    public function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    /**
     * Get processed
     *
     * @return boolean
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Set processed
     *
     * @param boolean $processed
     * @return Asset
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * Get lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return Asset
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lng
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set lng
     *
     * @param float $lng
     * @return Asset
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get meta
     *
     * @return string
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set meta
     *
     * @param string $meta
     * @return Asset
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

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
     * @return Asset
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * @return int
     */
    public function getAttempted()
    {
        return $this->attempted;
    }

    /**
     * Increase process attempted
     */
    public function increaseAttempted()
    {
        $this->attempted += 1;
        return $this;
    }

    /**
     * @return int
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param $message
     * @internal param $log
     * @return $this
     */
    public function addLog($message)
    {
        $this->log = $message . "\n" . $this->log;
        return $this;
    }

    /**
     * Get event
     *
     * @return \Api\V1\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set event
     *
     * @param \Api\V1\Entity\Event $event
     * @return Asset
     */
    public function setEvent(\Api\V1\Entity\Event $event = null)
    {
        $this->event = $event;

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
     * @return Asset
     */
    public function setUser(\Api\V1\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_ASSET;
    }

    /**
     * @return $this
     */
    public function reverseWithHeight()
    {
        $tmp = $this->getWidth();
        $this->setWidth($this->getHeight());
        $this->setHeight($tmp);
        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set width
     *
     * @param integer $width
     * @return Asset
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return Asset
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }
}
