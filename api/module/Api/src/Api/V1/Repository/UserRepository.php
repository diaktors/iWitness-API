<?php

namespace Api\V1\Repository;

use Api\V1\Entity\User;
use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\ORM\EntityRepository;
use Zend\XmlRpc\Value\DateTime;

class UserRepository extends EntityRepository
{
    /**
     * @param $xDay
     *
     * @return array of User
     */
    public function getXDaysExpire($xDay)
    {
        $xTime = $xDay * 24 * 60 * 60; //days to seconds
        $today = $this->getToday()->getTimestamp();
        $query = $this->createQueryBuilder('u')
            ->andWhere('u.subscriptionExpireAt >  0')
            ->andWhere('(u.subscriptionExpireAt - :xtime ) <= :today')
            ->andWhere('u.subscriptionExpireAt >=  :today')
            ->andWhere('(u.subscriptionExpireAt - :xtime ) >  u.subscriptionLastEmail  OR u.subscriptionLastEmail IS NULL')
            ->andWhere('u.flags <> :status')
            ->setParameter('status', User::STATUS_SUSPENDED)
            ->setParameter('xtime', $xTime)
            ->setParameter('today', $today)
            ->getQuery();
        $expiredUsers = $query->getResult();

        return $expiredUsers;
    }

    /**
     * @return array of User
     */
    public function getExpired()
    {
        $today = time();
        $query = $this->createQueryBuilder('u')
            ->andWhere('u.subscriptionExpireAt >  0')
            ->andWhere('u.flags <> :status')
            ->andWhere('u.subscriptionExpireAt <= :today AND u.subscriptionLastEmail < u.subscriptionExpireAt')
            ->setParameter('status', User::STATUS_SUSPENDED)
            ->setParameter('today', $today)
            ->getQuery();
        $expiredUsers = $query->getResult();

        return $expiredUsers;
    }

    /**
     * @return \DateTime
     */
    private function getToday()
    {
        $date = new \DateTime();
        $date->setTimestamp(time())->setTime(0, 0, 0);

        return $date;
    }

    /**
     * Search user
     *
     * @param $term
     *
     * @return mixed
     */
    public function findByPhoneNameEmail($term)
    {
        $sql = "
			SELECT  UUID_TO_STR(u.id) as id,  u.first_name as firstName, u.last_name as lastName, u.phone, u.secret_key
			FROM user as u
			WHERE deleted is NULL AND type = 1 ";

        if (is_numeric($term)) { //phone
            $sql .= " AND u.phone like :searchValue ";

        } elseif (filter_var($term, FILTER_VALIDATE_EMAIL)) { //email
            $sql .= " AND u.email like :searchValue  ";
        } else { //first name or last name
            $sql .= " AND ( u.first_name like :searchValue   OR  u.last_name  like :searchValue  )";
        }

        /** @var \Doctrine\DBAL\Statement $stmt */
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue("searchValue", "%$term%");
        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result;
    }
}



