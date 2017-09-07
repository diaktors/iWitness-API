<?php
/**
 * Created by PhpStorm.
 * User: hung
 * Date: 7/16/14
 * Time: 6:43 PM
 */

namespace Api\V1\Repository;

use Webonyx\Util\UUID;
use Api\V1\Entity\User;

class MessageRepository extends BaseRepository {

    /**
     * @param User $user
     * @return array
     */
    public function findActiveByUser(User $user){

        $query = $this->createQueryBuilder('m')
            ->select('m')
            ->join('Api\V1\Entity\UserMessage', 'um', "WITH", 'um.messageId = m.id')
            ->where('um.read = 0')
            ->andWhere('um.userId = :um_userId')
            ->setParameter('um_userId', UUID::toBinary($user->getId()))
            ->getQuery();
        $assets = $query->getResult();

        return $assets;
    }
} 