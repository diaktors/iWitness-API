<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_1028_20140826050646 extends ApiMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE `user` SET phone = CONCAT('1', phone) WHERE LENGTH(phone)=10");
    }

    public function down(Schema $schema)
    {
       ///cannot rollback
    }
}
