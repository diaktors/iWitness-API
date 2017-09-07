<?php

namespace Api\V1\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class BaseRepository extends EntityRepository
{

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getEntityManager();
    }
} 