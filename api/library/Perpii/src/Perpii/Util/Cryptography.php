<?php

namespace Perpii\Util;

use Zend\Crypt\Password\Bcrypt;

class Cryptography
{

    private function __construct()
    {

    }

    /**
     * @param $password
     * @return string
     */
    public static function createPassword($password)
    {
        $bcrypt = new Bcrypt;
        $bcrypt->setCost(10);
        return $bcrypt->create($password);
    }

    /**
     * @param $password
     * @param $hash
     * @return bool
     */
    public static function verify($password, $hash)
    {
        $bcrypt = new Bcrypt;
        $bcrypt->setCost(10);
        return $bcrypt->verify($password, $hash);
    }
} 