<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * Event
 *
 * @ORM\Table(name="event", indexes={@ORM\Index(name="FK_event_user", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="Api\V1\Repository\EventRepository")
 */
class Event implements ResourceInterface
{
    use SoftDeleteTrait;
    use AuditTrait;

    const NOT_PROCESS = 0; //default
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
     * @var float
     *
     * @ORM\Column(name="initial_lat", type="float", precision=10, scale=6, nullable=true)
     */
    private $initialLat;
    /**
     * @var float
     *
     * @ORM\Column(name="initial_long", type="float", precision=10, scale=6, nullable=true)
     */
    private $initialLong;
	/**
     * @var float
     *
     * @ORM\Column(name="final_lat", type="float", precision=10, scale=6, nullable=true)
     */
    private $finalLat;
    /**
     * @var float
     *
     * @ORM\Column(name="final_long", type="float", precision=10, scale=6, nullable=true)
     */
    private $finalLong;
    /**
     * @var integer
     *
     * @ORM\Column(name="processed", type="integer", nullable=false)
     */
    private $processed = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="log", type="string", nullable = true)
     */
    private $log;
    /**
     * @var integer
     *
     * @ORM\Column(name="flags", type="integer", nullable=false)
     */
    private $flags = '0';
    /**
     * @var integer
     *
     * @ORM\Column(name="attempted", type="integer", nullable = true)
     */
    private $attempted;
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
     * @ORM\OneToMany(targetEntity="Api\V1\Entity\Asset", mappedBy="event")
     * @var ArrayCollection
     **/
    private $assets;
    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="string", length=200, nullable=false)
     */
    private $duration;

    /**
     * @param string $uuid
     */
    public function __construct($uuid = null)
    {
        if ($uuid) {
            $this->id = $uuid;
            $this->assets = new ArrayCollection();
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
     * Get initialLat
     *
     * @return float
     */
    public function getInitialLat()
    {
        return $this->initialLat;
    }

    /**
     * Set initialLat
     *
     * @param float $initialLat
     * @return Event
     */
    public function setInitialLat($initialLat)
    {
        $this->initialLat = $initialLat;

        return $this;
    }

    /**
     * Get initialLong
     *
     * @return float
     */
    public function getInitialLong()
    {
        return $this->initialLong;
    }

    /**
     * Set initialLong
     *
     * @param float $initialLong
     * @return Event
     */
    public function setInitialLong($initialLong)
    {
        $this->initialLong = $initialLong;

        return $this;
    }
	
	 /**
     * Get finalLat
     *
     * @return float
     */
    public function getFinalLat()
    {
        return $this->finalLat;
    }

    /**
     * Set finalLat
     *
     * @param float $finalLat
     * @return Event
     */
    public function setFinalLat($finalLat)
    {
        $this->finalLat = $finalLat;

        return $this;
    }

    /**
     * Get finalLong
     *
     * @return float
     */
    public function getFinalLong()
    {
        return $this->finalLong;
    }

    /**
     * Set finalLong
     *
     * @param float $finalLong
     * @return Event
     */
    public function setFinalLong($finalLong)
    {
        $this->finalLong = $finalLong;

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
     * @return Event
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return Event
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

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
        $this->log = $message . $this->log;

        return $this;
    }

    /**
     * Get Assets
     *
     * @return Event
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $assets
     * @internal param \Doctrine\Common\Collections\ArrayCollection $asset
     *
     * @return $this
     */
    public function addAssets(ArrayCollection $assets)
    {
        $this->assets = $assets;
        /*$asset->setEvent($this);
        if (!$this->assets->contains($asset)) {
            $this->assets->add($asset);
        }*/

        return $this;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $assets
     * @internal param \Doctrine\Common\Collections\ArrayCollection $asset
     *
     * @return $this
     */
    public function  removeAssets(ArrayCollection $assets)
    {
        $this->assets = new ArrayCollection();
        /*if ($this->assets->contains($asset)) {
            $this->assets->remove($asset);
        }*/

        return $this;
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_EVENT;
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
     * Get duration
     *
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set duration
     *
     * @param string $duration
     * @return Event
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

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
     * Get array of asset ids only
     * @return array
     */
    public function getAssetIds()
    {
        $ids = array();
        foreach($this->assets as $asset) {
            $ids[] = $asset->getId();
        }
        return $ids;
    }

    /**
     * Get array of GPS location
     * @return array
     */
    public function getAssetGps()
    {
        $gps = array();
        foreach($this->assets as $asset) {
            $gps[] = [$asset->getLat(), $asset->getLng()];
        }
        return $gps;
    }

    /**
     * A new Asset was added to Event
     */
    public function newAssetProcessed()
    {
        $this->setProcessed(false); //reset flags
        $this->setFlags(self::NOT_PROCESS);
        $this->setModified(time()); //check this with old code
    }
}
