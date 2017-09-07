<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_1071_20140831021851 extends ApiMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $schema
            ->getTable('subscription')
            ->addColumn("purchased_token", "string")
            ->setlength(255)
            ->setnotnull(false);

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema
            ->getTable('subscription')
            ->dropColumn('purchased_token');
    }
}
