<?php

namespace Api\V1\Entity;

use Api\V1\Security\Authorization\AclAuthorization;
use Doctrine\ORM\Mapping as ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Perpii\Doctrine\Filter\SoftDeletable;

/**
 * @ORM\Entity
 */
class Admin extends User implements ResourceInterface, SoftDeletable
{

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return AclAuthorization::RESOURCE_ADMIN;
    }

    /**
     * @return bool
     */
    public function  isAdmin()
    {
        return true;
    }
}