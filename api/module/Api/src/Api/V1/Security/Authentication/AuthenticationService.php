<?php

namespace Api\V1\Security\Authentication;

use Api\V1\Entity\Admin;
use Api\V1\Entity\User;
use  Doctrine\ORM\EntityManager;

class AuthenticationService implements AuthenticationServiceInterface
{

    /** @var   \Doctrine\ORM\EntityManager */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param uuid $id
     * @return User | Admin
     */
    public function getIdentity($id)
    {
        $user = $this->entityManager->find('Api\V1\Entity\PersonAbstract', $id);
        if ($user instanceof User || $user instanceof Admin) {
            return $user;
        }
        return null;
    }
}