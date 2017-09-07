<?php

namespace Perpii\Util;


class  String
{
    private function __construct()
    {

    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * @param int $quantity
     * @param string $singular
     * @param string $plural
     * @return string
     */
    public static function pluralize($quantity, $singular, $plural)
    {
        if ($quantity <= 1) {
            return $plural;
        }
        return $singular;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return self::generateRandom($characters, $length);
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomNumber($length = 10)
    {
        return self::generateRandom('0123456789', $length);
    }

    /**
     * @param $characters
     * @param $length
     * @return string
     */
    private static function  generateRandom($characters, $length)
    {
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
} 