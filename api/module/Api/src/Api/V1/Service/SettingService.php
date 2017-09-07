<?php

namespace Api\V1\Service;

use Api\V1\Entity\Setting;
use Webonyx\Util\UUID;

class SettingService extends ServiceAbstract
{
    const ENTITY_CLASS = 'Api\V1\Entity\Setting';

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';

    const KEY_AUTHIRIZENET_LAST_UPDATE = 'a60fbe76-2e91-11e4-9c22-000c29c9a052';
    const KEY_PAYPAL_LAST_UPDATE = 'b7b9ed70-2e91-11e4-9c22-000c29c9a052';

    /**
     * @return int
     */
    public function getLastAuthorizeNetUpdate()
    {
        /** @var Setting $lastUpdate */
        $lastUpdate = $this->findByKey(self::KEY_AUTHIRIZENET_LAST_UPDATE);
        if (!$lastUpdate) {
            return 0;
        } else {
            return $lastUpdate->getValue();
        }
    }

    /**
     * @param $value
     * @return \Api\V1\Entity\Setting
     * @throws \InvalidArgumentException
     */
    public function saveLastAuthorizeNetUpdate($value)
    {
        return $this->save(self::KEY_AUTHIRIZENET_LAST_UPDATE, $value, self::TYPE_INTEGER);
    }

    /**
     * @param $key
     * @param $value
     * @param $type
     * @return \Api\V1\Entity\Setting
     * @throws \InvalidArgumentException
     */
    private function save($key, $value, $type)
    {
        $this->validateKey($key);

        $this->validateDataType($value, $type);

        /** @var Setting $setting */
        $setting = $setting = $this->findByKey($key);

        if ($setting) {
            $setting->setValue($value);
        } else {
            $setting = new Setting(UUID::generate(), $key, $type);
            $setting->setValue($value);
            $this->entityManager->persist($setting);
        }
        $this->entityManager->flush($setting);

        return $setting;
    }


    /**
     * @param $key
     * @return object
     */
    private function findByKey($key)
    {
        return $this->getRepository()->findOneBy(array('key' => $key));
    }


    /**
     * @param $key
     * @throws \InvalidArgumentException
     */
    private function validateKey($key)
    {
        if (!in_array($key, self::getAllowKeys())) {
            throw new \InvalidArgumentException('The key ' . $key . ' does not exist.');
        }
    }


    /**
     * @param $data
     * @param $type
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    private function validateDataType($data, $type)
    {
        $typeMapping = self::getTypeValidationMapping();

        if (!array_key_exists($type, $typeMapping)) {
            throw new \Exception('Type ' . $type . ' does not support');
        }

        if (!filter_var($data, $typeMapping[$type])) {
            throw new \InvalidArgumentException('Invalid data type');
        }
    }

    /**
     * @return array
     */
    private static function getAllowKeys()
    {
        static $allowKeys;

        if (!$allowKeys) {
            $allowKeys = array(self::KEY_AUTHIRIZENET_LAST_UPDATE, self::KEY_PAYPAL_LAST_UPDATE);
        }
        return $allowKeys;
    }

    /**
     * @return array
     * see http://www.w3schools.com/php/php_ref_filter.asp
     */
    private static function getTypeValidationMapping()
    {
        static $mapping;
        if (!$mapping) {
            $mapping = array(self::TYPE_STRING => FILTER_SANITIZE_STRING, self::TYPE_INTEGER => FILTER_VALIDATE_INT);
        }
        return $mapping;
    }
}