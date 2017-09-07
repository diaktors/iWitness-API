<?php

namespace Api\V1\Repository {

    use Webonyx\Util\UUID;
    use Api\V1\Entity\User;

    class DeviceRepository extends BaseRepository
    {
        /**
         * @param User $user
         * @return array of Devices
         */
        public function findByUser(User $user)
        {
            $query = $this->createQueryBuilder('d')
                ->select('d')
                ->join('Api\V1\Entity\UserDevice', 'ud', "WITH", 'ud.deviceId = d.id')
                ->where('ud.userId  = :ud_userId')
                ->setParameter('ud_userId', UUID::toBinary($user->getId()))
                ->getQuery();
            $assets = $query->getResult();
            return $assets;
        }
    }
}