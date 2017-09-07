<?php

namespace Api\V1\Security\Role;

interface  RoleProviderInterface
{
    /**
     * @param $entity
     * @param string | entity $resource
     * @return string array
     */
    public function getRoleFor($entity, $resource);
}