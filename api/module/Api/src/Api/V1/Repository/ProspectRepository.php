<?php

namespace Api\V1\Repository;

use Api\V1\Entity\Prospect;
use Doctrine\ORM\EntityRepository;
use Webonyx\Util\UUID;

class ProspectRepository extends  EntityRepository
{

    /**
     * @param $email
     * @param $platform
     * @return Prospect
     */
    public function insertOrUpdate($email, $platform)
    {
        $entityManager = $this->getEntityManager();
        /** @var Prospect $prospect */
        $prospect = $this->findOneBy(array('email' => $email));
        if ($prospect) {
            $prospect->setPlatform($platform);
        } else {
            $senderId = UUID::generate();
            $prospect = new Prospect($senderId);
            $prospect
                ->setPlatform($platform)
                ->setEmail($email);
            $entityManager->persist($prospect);
        }
        $entityManager->flush($prospect);

        return $prospect;
    }
}
