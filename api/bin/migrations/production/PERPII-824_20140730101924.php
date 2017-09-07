<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class perpii_824_20140730101924 extends ApiMigration
{
    public function up(schema $schema)
    {
        $table = $schema->createtable('plan');
        $table->addcolumn("id", "uuid")->setlength(16)->setnotnull(true);
        $table->addcolumn("key", "string")->setlength(50)->setnotnull(true);
        $table->addcolumn("name", "string")->setlength(50)->setnotnull(true);
        $table->addcolumn("description", "string")->setlength(255)->setnotnull(false);
        $table->addcolumn("price", "float")->setprecision(10)->setscale(0)->setnotnull(true)->setdefault(0);
        $table->addcolumn("length", "integer")->setnotnull(true)->setdefault(0)->setcomment('duration in month');
        $table->addcolumn("flags", "integer")->setnotnull(true)->setdefault(0);
        $table->addcolumn("created", "integer")->setnotnull(true);
        $table->addcolumn("modified", "integer")->setnotnull(false);
        $table->addcolumn("deleted", "integer")->setnotnull(false);
        $table->setprimarykey(array("id"));
        $table->addUniqueIndex(array("`key`"));
    }

    public function down(schema $schema)
    {
        $schema->droptable('plan');

    }
}
