<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_744_20140804095735 extends ApiMigration
{
    public function up(Schema $schema)
    {
        $schema->getTable('contact')->addColumn("secret_key", "string")->setlength(50)->setnotnull(false);
    }

    public function down(Schema $schema)
    {
        $schema->getTable('contact')->dropColumn('secret_key');

    }
}
