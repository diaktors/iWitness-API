<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_1127_20140919074023 extends ApiMigration
{
    public function up(Schema $schema)
    {
        $schema
            ->getTable('asset')
            ->dropColumn('created_at')
            ->dropColumn('name')
            ->dropColumn('display_name');

        $schema
            ->getTable('event')
            ->dropColumn('display_name');

    }

    public function down(Schema $schema)
    {
        $table = $schema->getTable('asset');

        $table
            ->addcolumn("created_at", "integer")
            ->setLength(10)
            ->setnotnull(false);

        $table->addColumn("name", "string")
            ->setlength(255)
            ->setnotnull(false);

        $table->addColumn("display_name", "string")
            ->setlength(255)
            ->setnotnull(false);

        $schema
            ->getTable('event')
            ->addColumn("display_name", "string")
            ->setlength(255)
            ->setnotnull(false);
    }
}
