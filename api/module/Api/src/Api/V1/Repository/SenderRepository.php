<?php

namespace Api\V1\Repository;

use Api\V1\Entity\Sender;
use Webonyx\Util\UUID;
use Doctrine\ORM\EntityRepository;

class SenderRepository extends EntityRepository
{
    /**
     * @param $name
     * @param $email
     * @return Sender
     */
    public function insertOrUpdate($name, $email)
    {
        $entityManager = $this->getEntityManager();

        /** @var Sender $sender */
        $sender = $this->findOneBy(array('email' => $email));
        if ($sender) {
            $sender->setFirstName($name);
        } else {
            $senderId = UUID::generate();
            $sender = new Sender($senderId);
            $sender
                ->setFirstName($name)
                ->setEmail($email);
            $entityManager->persist($sender);
        }
        $entityManager->flush($sender);
        return $sender;
    }
} 