<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_1029_20140828083533 extends ApiMigration
{
    public function up(Schema $schema)
    {
        if (!$schema->hasTable('setting')) {
            $table = $schema->createtable('setting');
            $table->addcolumn("id", "uuid")->setlength(16)->setnotnull(true);
            $table->addcolumn("data_key", "string")->setlength(50)->setnotnull(true);
            $table->addcolumn("data_type", "string")->setlength(20)->setnotnull(true);
            $table->addcolumn("data_value", "string")->setlength(255)->setnotnull(false);
            $table->addcolumn("created", "integer")->setnotnull(true);
            $table->addcolumn("modified", "integer")->setnotnull(false);
            $table->addcolumn("deleted", "integer")->setnotnull(false);
            $table->setprimarykey(array("id"));
            $table->addUniqueIndex(array("`data_key`"));
        }
    }

    public function down(Schema $schema)
    {
        if ($schema->hasTable('setting')) {
            $schema->dropTable('setting');
        }
    }
}
