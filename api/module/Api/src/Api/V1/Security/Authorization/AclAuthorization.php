<?php

namespace Api\V1\Security\Authorization;

use Api\V1\Security\Role\RoleProviderInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Permissions\Acl\Role\GenericRole as Role;


class AclAuthorization extends Acl implements AuthorizationInterface
{
    const ROLE_GUEST = 'guest';
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_LOGIN = 'login';

    const RESOURCE_CONTACT = 'contact';
    const RESOURCE_SUBSCRIPTION = 'subscription';
    const RESOURCE_ASSET = 'asset';
    const RESOURCE_COUPON = 'coupon';
    const RESOURCE_EVENT = 'event';
    const RESOURCE_INVITATION = 'invitation';
    const RESOURCE_EMERGENCY = 'emergency';
    const RESOURCE_PROSPECT = 'prospect';
    const RESOURCE_USER = 'subscriber';
    const RESOURCE_SENDER = 'receiver';
    const RESOURCE_ADMIN = 'admin';
    const RESOURCE_DEVICE = 'device';
    const RESOURCE_MESSAGE = 'message';
    const RESOURCE_PLAN = 'plan';
    const RESOURCE_SETTING = 'setting';
    const RESOURCE_EMAIL_FALLBACK = 'fallback';

    const PERMISSION_CREATE = 'create';
    const PERMISSION_UPDATE = 'update';
    const PERMISSION_DELETE = 'delete';
    const PERMISSION_VIEW = 'view';
    const PERMISSION_LIST_ALL = 'list_all';
    const PERMISSION_CHANGE_PASSWORD = 'change_password';

    /** @var  RoleProviderInterface */
    private $roleProvider;


    public function __construct(RoleProviderInterface $roleProvider)
    {
        $this->roleProvider = $roleProvider;
        $this->init();
    }

    protected function  init()
    {
        $this->addRole(new Role(self::ROLE_GUEST))
            ->addRole(new Role(self::ROLE_LOGIN), array(self::ROLE_GUEST))
            ->addRole(new Role(self::ROLE_OWNER), array(self::ROLE_LOGIN))
            ->addRole(new Role(self::ROLE_ADMIN), array(self::ROLE_LOGIN));


        $this->addResource(new Resource(self::RESOURCE_CONTACT))
            ->addResource(new Resource(self::RESOURCE_SUBSCRIPTION))
            ->addResource(new Resource(self::RESOURCE_ASSET))
            ->addResource(new Resource(self::RESOURCE_COUPON))
            ->addResource(new Resource(self::RESOURCE_EVENT))
            ->addResource(new Resource(self::RESOURCE_USER))
            ->addResource(new Resource(self::RESOURCE_PROSPECT))
            ->addResource(new Resource(self::RESOURCE_SENDER))
            ->addResource(new Resource(self::RESOURCE_INVITATION))
            ->addResource(new Resource(self::RESOURCE_EMERGENCY))
            ->addResource(new Resource(self::RESOURCE_ADMIN))
            ->addResource(new Resource(self::RESOURCE_MESSAGE))
            ->addResource(new Resource(self::RESOURCE_PLAN))
            ->addResource(new Resource(self::RESOURCE_DEVICE));

        $this->allow(
            self::ROLE_GUEST,
            array(
                self::RESOURCE_USER,
                self::RESOURCE_SUBSCRIPTION,
                self::RESOURCE_INVITATION,
                self::RESOURCE_EMERGENCY
            ),
            array(self::PERMISSION_CREATE)
        );

        $this->allow(self::ROLE_ADMIN, null, array(
                self::PERMISSION_CREATE,
                self::PERMISSION_UPDATE,
                self::PERMISSION_DELETE,
                self::PERMISSION_CHANGE_PASSWORD,
                self::PERMISSION_VIEW,
                self::PERMISSION_LIST_ALL)
        );

        $this->allow(self::ROLE_OWNER, null, array(
                self::PERMISSION_CREATE,
                self::PERMISSION_UPDATE,
                self::PERMISSION_DELETE,
                self::PERMISSION_CHANGE_PASSWORD,
                self::PERMISSION_VIEW)
        );

        $this->allow(self::ROLE_GUEST, self::RESOURCE_PLAN,
            array(
                self::PERMISSION_VIEW,
                self::PERMISSION_LIST_ALL
            )
        );

        $this->allow(
            self::ROLE_LOGIN,
            array(self::RESOURCE_CONTACT, self::RESOURCE_EVENT, self::RESOURCE_ASSET, self::RESOURCE_DEVICE),
            self::PERMISSION_CREATE
        );
    }

    /**
     * @param $identity
     * @param mixed $resource
     * @param mixed $privilege
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function isAuthorized($identity, $resource, $privilege)
    {
        if (empty($resource)) {
            throw new \InvalidArgumentException('Resource could not be null to check permission on it');
        }

        if (empty($privilege)) {
            throw new \InvalidArgumentException('Privilege could not be null');
        }

        $roles = $this->roleProvider->getRoleFor($identity, $resource);

        return $this->isAllow($roles, $resource, $privilege);
    }

    /**
     * @param $roles
     * @param $resource
     * @param $privilege
     * @return bool
     */
    private function  isAllow($roles, $resource, $privilege)
    {
        $isAllow = false;
        foreach ($roles as $role) {
            if ($this->isAllowed($role, $resource, $privilege)) {
                $isAllow = true;
                break;
            }
        }
        return $isAllow;
    }

}
