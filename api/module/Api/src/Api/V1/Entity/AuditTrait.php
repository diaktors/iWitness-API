<?php

namespace Api\V1\Entity;


trait AuditTrait
{
    /**
     * @var integer
     *
     * @ORM\Column(name="created", type="integer", nullable=false)
     */
    private $created;

    /**
     * @var integer
     *
     * @ORM\Column(name="modified", type="integer", nullable=true)
     */
    private $modified;


    /**
     * Get created
     *
     * @return integer
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get modified
     *
     * @return integer
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set modified
     *
     * @param integer $modified
     * @return \Api\V1\Entity\Contact
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }
}