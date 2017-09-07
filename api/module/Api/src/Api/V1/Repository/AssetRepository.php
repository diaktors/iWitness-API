<?php

namespace Api\V1\Repository;

use Api\V1\Entity\Asset;
use Doctrine\ORM\EntityRepository;
use Webonyx\Util\UUID;

class AssetRepository extends EntityRepository
{
    /**
     * @param $eventId
     * @return array
     */
    public function fetchForMerging($eventId)
    {
        $query = $this->createQueryBuilder('a')
            ->join('a.event', 'e')
            ->where('e.id = :eventId')
            ->andWhere('a.flags = :flags')
            ->andWhere('a.processed = 1')
            ->setParameter('eventId', UUID::toBinary($eventId))
            ->setParameter('flags', Asset::SUCCESS)
            ->orderBy('a.created', 'ASC')
            //->orderBy('a.modified', 'ASC')
            ->getQuery();

        $assets = $query->getResult();
        return $assets;
    }

    /**
     * @param $max
     * @return array
     */
    public function fetchForProcessing($max)
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.processed = 0')
            ->orderBy('a.created', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults($max)
            ->getQuery();

        $assets = $query->getResult();
        return $assets;
	}

	/**
	 ** @param $max
	 ** @return array
	 **/
	public function fetchlastAssests($userId)
	{
		$query = $this->createQueryBuilder('a')
			->where('a.stopped = 1')
			->andWhere("a.userid_text = '".$userId."'")
			->orderBy('a.uptime', 'DESC')
			->setFirstResult(0)
			->setMaxResults(1)
			->getQuery();

		$assets = $query->getResult();
		return $assets;
	}
} 
