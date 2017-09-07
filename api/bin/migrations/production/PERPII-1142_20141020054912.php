<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;


class PERPII_1142_20141020054912 extends ApiMigration
{
    public function up(Schema $schema)
    {

        $sql = "
                INSERT INTO `plan` (`id`, `key`, `name`, `description`, `price`, `length`, `flags`, `created`, `modified`, `deleted`)
                VALUES (UUID_TO_BIN('18f358ac-581d-11e4-ac52-000c29c9a052'), 'student', 'student', 'Special Student Pricing', 14.95, 12, 0, UNIX_TIMESTAMP(), NULL, NULL)
                ";
        $this->addSql($sql);

    }

    public function down(Schema $schema)
    {
        $ids = array(
            '18f358ac-581d-11e4-ac52-000c29c9a052',
        );

        foreach ($ids as $id) {
            $sql = "DELETE FROM `plan` WHERE id = `UUID_TO_BIN`('" . $id . "')";
            $this->addSql($sql);
        }
    }
}
