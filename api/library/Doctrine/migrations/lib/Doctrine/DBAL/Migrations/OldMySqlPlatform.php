<?php
/**
 * Created by PhpStorm.
 * User: corybohon
 * Date: 3/24/14
 * Time: 4:36 PM
 */

namespace Doctrine\DBAL\Migrations;

use Doctrine\DBAL\Platforms\MySqlPlatform;

class OldMySqlPlatform extends MySqlPlatform
{

    /**
     * {@inheritDoc}
     *
     * Adding Datetime2 Type
     */
    protected function initializeDoctrineTypeMappings()
    {
        parent::initializeDoctrineTypeMappings();
        $this->doctrineTypeMapping['point'] = 'string';
        $this->doctrineTypeMapping['enum'] = 'string';
    }

    public function import(MySqlPlatform $object)
    {
        $refObject = new \ReflectionObject($object);
        foreach ($refObject->getProperties() as $prop) {
            $prop->setAccessible(true);
            $name = $prop->getName();
            $this->$name = $prop->getValue($object);
        }
    }
} 