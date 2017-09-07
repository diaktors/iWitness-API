<?php

namespace Api\V1\Security\Role;

use Api\V1\Entity\Admin;
use Api\V1\Entity\Contact;
use Api\V1\Entity\Coupon;
use Api\V1\Entity\Event;
use Api\V1\Entity\Prospect;
use Api\V1\Entity\Subscription;
use Api\V1\Entity\User;
use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\ORM\EntityManager;
use DoctrineModule\ValidatorNoObjectExists;
use Psr\Log\LoggerInterface;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class UserRoleProvider implements RoleProviderInterface
{
    /** @var  EntityManager */
    private $entityManager;

    /** @var LoggerInterface */
    private $logger = null;


    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     * @param $identity
     * @param entity|string $resource
     * @return array
     */
    public function getRoleFor($identity, $resource)
    {
        if (!$identity) {
            return array(AclAuthorization::ROLE_GUEST);
        }

        if ($identity instanceof Admin) {
            return array(AclAuthorization::ROLE_ADMIN);
        }

        if (is_string($resource)) {
            return $this->getRolesForResourceString($identity, $resource);
        } else {
            return $this->getRolesForResourceEntity($identity, $resource);
        }
    }

    /**
     * @param User $identity
     * @param $resource
     * @return array
     */
    private function getRolesForResourceString(User $identity, $resource)
    {
        $roles = array(AclAuthorization::ROLE_LOGIN);
        return $roles;
    }

    /**
     * @param User $identity
     * @param $resource
     * @return array
     */
    private function getRolesForResourceEntity(User $identity, $resource)
    {
        $this->logger->debug('Get role for identity ' . $identity->getId() . ' resource class  ' . get_class($resource));

        $roles = array(AclAuthorization::ROLE_LOGIN);

        if ($resource instanceof User) {
            if (strcmp($resource->getId(), $identity->getId()) == 0) {
                $roles[] = AclAuthorization::ROLE_OWNER;
            }
        } else {
            $associatedUser = null;
            if ($resource instanceof Contact) {
                $associatedUser = $resource->getUser();
            } else if ($resource instanceof Subscription) {
                $associatedUser = $resource->getUser();
            } else if ($resource instanceof Event) {
                $associatedUser = $resource->getUser();
            }

            if ($associatedUser) {
                $roles += $this->getRoleFor($identity, $associatedUser);
            }
        }
        return $roles;
    }
}