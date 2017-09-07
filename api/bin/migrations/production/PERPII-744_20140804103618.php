<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_744_20140804103618 extends ApiMigration
{
    public function up(Schema $schema)
    {
        $sql = "UPDATE  `contact` SET `secret_key` =  uuid()
                WHERE `secret_key` IS NULL";

        $this->addSql($sql);
    }

    public function down(Schema $schema)
    {

    }
}
