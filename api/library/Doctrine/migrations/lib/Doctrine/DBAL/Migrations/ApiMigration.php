<?php

namespace Doctrine\DBAL\Migrations;


abstract class ApiMigration extends AbstractMigration
{

    public function __construct(Version $version)
    {
        parent::__construct($version);

        try {
            \Doctrine\DBAL\Types\Type::addType('flags', 'Doctrine\DBAL\Types\BitField');
            \Doctrine\DBAL\Types\Type::addType('uuid', 'Doctrine\DBAL\Types\UUID');
        } catch (\Exception $ex) {

        }
    }
}