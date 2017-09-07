<?php


namespace Api\V1\Repository;

use Doctrine\ORM\EntityRepository;

class GiftCardRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getTodayDeliverGift()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $from = $today->getTimestamp();
        $to = $from + (24 * 60 * 60);

        $query = $this->createQueryBuilder('g')
            ->where('g.isDelivered = false')
            ->andWhere('g.deliveryDate <= :to')
            ->setParameter('to', $to)
            ->orderBy('g.deliveryDate', 'DESC')
            ->getQuery();

        $giftCards = $query->getResult();

        return $giftCards;
    }


} 