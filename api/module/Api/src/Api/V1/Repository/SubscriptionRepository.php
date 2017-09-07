<?php


namespace Api\V1\Repository;

use Api\V1\Entity\User;
use Doctrine\ORM\EntityRepository;

class SubscriptionRepository extends EntityRepository
{
    /**
     *
     * @param $from
     * @param $to
     * @return array
     */
    public function getRevenue($from, $to)
    {
        $sql = "SELECT  FROM_UNIXTIME(created, '%m-%d-%Y') AS created
	                    ,plan
                        ,count(plan) as total
                        ,sum(amount) as revenue
                FROM (
                      SELECT created, plan,
                             CASE plan
    					        WHEN 'giftplanyear' THEN 0
    							ELSE amount
							 END  AS amount
                       FROM subscription
                       WHERE created >=:from AND created <= :to
                ) AS sub
                GROUP BY FROM_UNIXTIME(created, '%m-%d-%Y'), plan
                ORDER BY   created DESC
                ";


        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue("from", $from);
        $stmt->bindValue("to", $to);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    /**
     * Get Subscriptions by ExpiredUsers
     * return array of Subscriptions
     */
    public function getByExpiredUsers($paymentGateway='')
	{
		$today = time();
		$startat = 1477060836;
        $queryBuilder = $this->createQueryBuilder('s');
        $query = $queryBuilder
            ->select('s')
            ->innerJoin('Api\\V1\\Entity\\User', 'u')
            ->where('u.subscriptionId = s.id')
            ->andWhere('s.paymentGateway = :paymentGateway')
            ->andWhere('u.subscriptionExpireAt >  0')
            ->andWhere('u.subscriptionExpireAt <= :today')
            ->andWhere('u.subscriptionStartAt >= :startat')
            ->andWhere('u.flags <> :status')
            ->setParameter('paymentGateway', $paymentGateway)
            ->setParameter('today', $today)
            ->setParameter('startat', $startat)
            ->setParameter('status', User::STATUS_SUSPENDED)
			->getQuery();
		/*$sql ="select * from subscription as s INNER JOIN user as u where  u.subscription_id=s.id and u.subscription_start_at>=$start_at and u.subscription_expire_at >0 and u.subscription_expire_at<=$today and u.flags!=1 and s.payment_gateway='$paymentGateway'";
		$stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
		$subscriptions = $stmt->fetchAll();*/
	//	$q=$query->getSQL();
	//	error_log("Query: ".$q."\n" , 3, "/volumes/log/api/test-log.log");
	//	error_log("Parameters: ".print_r($query->getParameters(), TRUE)."\n" , 3, "/volumes/log/api/test-log.log");
        $subscriptions = $query->getResult();

        return $subscriptions;
    }
}
