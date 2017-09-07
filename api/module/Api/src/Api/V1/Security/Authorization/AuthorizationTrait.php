<?php

namespace Api\V1\Security\Authorization;

use Api\V1\Entity\User;
use Api\V1\Security\Authentication\AuthenticationTrait;
use ZF\ApiProblem\ApiProblem;


trait  AuthorizationTrait
{
    /** @var AuthorizationInterface */
    protected $authorization;

    use AuthenticationTrait;

    /**
     * @param AuthorizationInterface $authorization
     */
    public function setAuthorization(AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @param $resource
     * @param $privilege
     * @param bool $checkExpired
     * @param string $message
     * @return bool|\ZF\ApiProblem\ApiProblem
     */
    protected function isAuthorized($resource, $privilege, $checkExpired = true, $message = null)
    {
        $message = $message != null ? $message : 'Unauthorized';

        $identity = $this->getIdentity();
        if (!$identity) {
            $message = "User not logged in";
            return new ApiProblem(401, $message);
        }

        if (($checkExpired) && $identity->hasExpired()) {
            return new ApiProblem(401, 'Your account was expired, please renew it', null, 'Expired');
        }

        if ($identity->isSuspended()) {
            return new ApiProblem(401, 'Your account was suspended');
        }

        if ($this->authorization->isAuthorized($identity, $resource, $privilege)) {
            return true;
        } else {
            return new ApiProblem(401, $message);
        }
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @throws \Exception
     * @return string
     */
    abstract function getResourceId();

} 