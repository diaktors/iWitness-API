<?php

namespace Perpii\Util;


class Token {

    private function __construct()
    {

    }

    /**
     * Signs string with hash, based on secret signature and returns url-safe
     * base64 representation of signed string.
     *
     * Note that signature itself is never exposed in final string.
     *
     * @param string $unsigned
     * @param string $secret
     * @return string
     */
    public static function sign($unsigned, $secret)
    {
        $hash = hash_hmac('sha1', $unsigned, $secret);
        $signed = $hash . ':' . $unsigned;

        // see RFC3548, section 4
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($signed));
    }

    /**
     * Extracts unsigned value from signed string without signature validation.
     * In case if unsigned value cannot be extracted returns false
     *
     * @param string $signed
     * @return string
     */
    public static  function extractUnsigned($signed)
    {
        $signed = base64_decode(str_replace(array('-', '_'), array('+', '/'), $signed));
        list ($hash, $unsigned) = explode(':', $signed, 2) + array(false, false);
        return $unsigned;
    }

    /**
     * Returns original unsigned string only of string signature matches
     * signature generated from secret. If signatures do not match return false
     *
     * @param string $signed
     * @param string $secret
     * @return string|false
     */
    public static  function unsign($signed, $secret)
    {
        if (!is_string($signed) || !$signed) {
            return false;
        }
        $signed = base64_decode(str_replace(array('-', '_'), array('+', '/'), $signed));
        $parts  = explode(':', $signed, 2);

        if (2 != count($parts)) {
            return false;
        }

        list($hash, $string) = $parts;

        if ($hash !== hash_hmac('sha1', $string, $secret)) {
            // signature doesn't match
            return false;
        }

        return $string;
    }


} 