<?php

namespace Api\V1\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Webonyx\Util\UUID;

class EventRepository extends EntityRepository
{
    /**
     * @param $max
     * @return array
     */
    public function fetchForProcessing($max)
    {

     /*$query = $this->createQueryBuilder('e')
            ->select('e', 'a')
            ->leftJoin('e.assets', 'a')
            ->where('e.processed = 0')
            //andWhere('a.processed = 0')
            ->andWhere('e.modified > 0')
            ->andWhere('e.modified<:last2minutes')
            ->setParameter('last2minutes', time() - 2 * 60)
            ->orderBy('a.uptime', 'ASC')
            ->groupBy('a.video_id')
            ->setFirstResult(0)
            ->setMaxResults($max)
            ->getQuery();

        $assets = $query->getResult();
	 return $assets;*/
 

      $query = $this->createQueryBuilder('e')
            ->where('e.processed = 0')
            ->andWhere('e.modified > 0')
            ->andWhere('e.modified<:last2minutes')
            ->setParameter('last2minutes', time() - 2 * 60)
            ->orderBy('e.modified', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults($max)
            ->getQuery();

        $assets = $query->getResult();
	  return $assets;
    }

    /**
     * Get events by user
     * @param $userId
     * @return mixed
     */
    public function fetchForUser($userId)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.processed <> 0')
            ->andWhere('e.user =:user_id')
            ->setParameter('user_id', UUID::toBinary($userId))
            ->orderBy('e.modified', 'ASC')
            ->getQuery();

        $assets = $query->getResult();
        return $assets;
    }

    /**
     * Get event with specific {id} and fetch all assets if any
     * @param $eventId
     * @return \Api\V1\Entity\Event
     */
    public function fetchWithAsset($eventId)
    {
        $query = $this->createQueryBuilder('e')
            ->select('e', 'a')
            ->leftJoin('e.assets', 'a')
            ->andWhere('e.id = :eventId')
            ->setParameter('eventId', UUID::toBinary($eventId))
            ->getQuery();

        $event = $query->getSingleResult();
        return $event;
    }
}
