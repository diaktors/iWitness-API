<?php

namespace Api\V1\Entity;


trait SoftDeleteTrait
{
    /**
     * @var integer
     *
     * @ORM\Column(name="deleted", type="integer", nullable=true)
     */
    private $deleted;

    /**
     * Get deleted
     *
     * @return integer
     */
    public function isDeleted()
    {
        return $this->deleted != null;
    }

    /**
     * Set deleted
     *
     * @param integer $deleted
     * @return Asset
     */
    public function setDeleted($deleted)
    {
        if ($deleted == 0) {
            $deleted = null;
        }

        $this->deleted = $deleted;

        return $this;
    }
} 