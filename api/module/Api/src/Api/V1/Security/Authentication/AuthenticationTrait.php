<?php

namespace Api\V1\Security\Authentication;

trait AuthenticationTrait
{

    /** @var  \Api\V1\Security\Authentication\AuthenticationService */
    protected $authentication = null;

    /** @var bool */
    private $hasLoadedUser = false;

    /** @var null | \Api\V1\Entity\User */
    private $currentUser;


    /**
     * @param AuthenticationService $authentication
     */
    public function setAuthentication(AuthenticationService $authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * Get current login user
     * @return null| \Api\V1\Entity\User
     */
    public function getIdentity()
    {
        if ($this->hasLoadedUser) {
            return $this->currentUser;
        }
        $identity = parent::getIdentity();

        if ($identity) {
            $authenticationIdentity = $identity->getAuthenticationIdentity();
            if ($authenticationIdentity && isset($authenticationIdentity['user_id'])) {
                $id = $authenticationIdentity['user_id'];
                $this->currentUser = $this->authentication->getIdentity($id);
            }
        }

        return $this->currentUser;
    }
} 