<?php

namespace Api\V1\Service\Extension;


use Perpii\Util\Token;

use Api\V1\Service\Exception\BusinessException;

trait TokenTrait
{

    /**
     * Generates timestamped token for given user or contact.
     * This token may be used as temporary person identifier.
     *
     * @param $entity
     * @param $role
     * @throws BusinessException
     * @return string
     */
    public function generateToken($entity, $role)
    {
        if (!method_exists($entity, 'getSecretKey')) {
            throw new BusinessException("Invalid entity object ", 404);
        }

        $secret = $entity->getSecretKey();
        if (!$secret) {
            throw new BusinessException("Secret key does not exist in entity", 404);
        }

        $token = Token::sign(time() . ':' . $role . ':' . $entity->getId(), $secret);
        return $token;
    }


    /**
     *  Validates token and if valid - returns related object
     * @param $token
     * @param $expectedRole
     * @param null $expireHours
     * @return object
     * @throws BusinessException
     */
    public function assertValidToken($token, $expectedRole, $expireHours = null)
    {
        // 1. Find person record:
        if (false === ($unsigned = Token::extractUnsigned($token))) {
            throw new BusinessException("Not valid token format", 412);
        }

        list ($time, $role, $id) = explode(':', $unsigned, 3) + array(0, '', '');

        //get current Entity
        $entity = $this->getRepository()->find($id);
        if (!$entity) {
            throw new BusinessException("Entity does not exist", 404);
        }

        if (!method_exists($entity, 'getSecretKey')) {
            throw new BusinessException("Invalid entity object ", 404);
        }

        $secret = $entity->getSecretKey();
        if (!$secret) {
            throw new BusinessException("Secret key does not exist in entity", 404);
        }

        // 2. Make sure that token was signed properly:
        if (false == Token::unsign($token, $secret)) {
            throw new BusinessException("Not a valid token format", 404);
        }

        // 3. Make sure that token is not expired:
        if ($expireHours && $time + $expireHours * 3600 < time()) {
            //throw new BusinessException("Token has expired", 404);
            throw new BusinessException("Link has expired", 404);
        }

        if ($role != $expectedRole) {
            throw new BusinessException("Invalid role", 404);
        }

        return $entity;
    }



    /**
     * Generates timestamped token for given user or contact.
     * This token may be used as temporary person identifier.
     *
     * @param $entity
     * @param $token
	 * @param $role
     * @throws BusinessException
     * @return string
     */
    public function generatePasswordToken($entity, $token, $role)
    {
        if (!method_exists($entity, 'getSecretKey')) {
            throw new BusinessException("Invalid entity object ", 404);
        }

        $secret = $token;
        if (!$secret) {
            throw new BusinessException("Secret key does not exist in entity", 404);
        }
        $token = Token::sign(time() . ':' . $role . ':' . $entity->getId(), $secret);
        return $token;
    }
}
