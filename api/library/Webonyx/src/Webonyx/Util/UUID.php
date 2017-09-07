<?php
namespace Webonyx\Util;


class UUID
{

    const UUID_FMT_STR = 1;
    const UUID_FMT_BIN = 2;

    const UUID_MAKE_V1 = UUID_TYPE_TIME;
    const UUID_MAKE_V5 = UUID_TYPE_DEFAULT;

    /**
     * @static
     * @param bool $binary
     * @return string
     */
    public static function generate($binary = false) {
        return self::v1($binary);
    }

    /**
     * returns a type 1 (MAC address and time based) Webonyx_UUID
     * @param bool $binary
     * @return string
     */
    public static function v1($binary = false) {
        $uuid = uuid_create(self::UUID_MAKE_V1);
        return $binary ?
            self::toBinary($uuid) : $uuid;
    }

    /**
     * returns a type 5 (SHA-1 hash) Webonyx_UUID
     * @param bool $binary
     * @return string
     */
    public static function v5($binary = false) {
        $uuid = uuid_create(self::UUID_MAKE_V5);
        return $binary ?
            self::toBinary($uuid) : $uuid;
    }

    /**
     * @static
     * @param string $uuid
     * @param int $toFmt
     * @return string
     */
    public static function convert($uuid, $toFmt) {
        $fromFmt = self::isBinary($uuid) ? self::UUID_FMT_BIN : self::UUID_FMT_STR;

        if($fromFmt == self::UUID_FMT_BIN && $toFmt == self::UUID_FMT_STR) {
            return uuid_unparse($uuid);
        } else if($fromFmt == self::UUID_FMT_STR && $toFmt == self::UUID_FMT_BIN) {
            return uuid_parse($uuid);
        } else {
            return $uuid;
        }
    }

    /**
     * @static
     * @param string $uuid
     * @return string
     */
    public static function toStr($uuid) {
        return self::convert($uuid, self::UUID_FMT_STR);
    }

    public static function addDashes($uuid)
    {

        return substr($uuid, 0, 8) . '-'.
        substr($uuid, 8, 4) . '-'.
        substr($uuid, 12, 4) . '-'.
        substr($uuid, 16, 4) . '-'.
        substr($uuid, 20);

    }
    /**
     * @static
     * @param string $uuid
     * @return string
     */
    public static function toBinary($uuid)
    {
        return self::convert($uuid, self::UUID_FMT_BIN);
    }

    /**
     * @static
     * @param string $uuid
     * @return int
     */
    public static function getTimestamp($uuid)
    {
        return uuid_time($uuid);
    }

    /**
     * @static
     * @param $uuidStr
     * @return bool
     */
    public static function validUUID($uuidStr) {
        return uuid_is_valid($uuidStr);
    }

    /**
     * @static
     * @param string $uuid
     * @return int
     */
    public static function isBinary($uuid) {
        return preg_match('/((?![\x20-\x7E]).)/', $uuid);
    }
}
