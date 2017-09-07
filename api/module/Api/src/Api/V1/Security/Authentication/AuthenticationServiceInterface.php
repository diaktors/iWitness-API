<?php

namespace Api\V1\Security\Authentication;


interface AuthenticationServiceInterface
{

    /**
     * @param $id
     * @return mixed
     */
    public function getIdentity($id);

} 