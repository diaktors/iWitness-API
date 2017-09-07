<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_1099_20140916075819 extends ApiMigration
{
    public function up(schema $schema)
    {
        $table = $schema->createtable('email_fallback');
        $table->addcolumn("id", "uuid")->setlength(16)->setnotnull(true);
        $table->addcolumn("email_id", "integer")->setnotnull(true);
        $table->addcolumn("created", "integer")->setnotnull(true);
        $table->addcolumn("modified", "integer")->setnotnull(false);
        $table->addcolumn("deleted", "integer")->setnotnull(false);
        $table->setprimarykey(array("id"));
        $table->addUniqueIndex(array("`email_id`"));
        $table->addIndex(array('email_id'), 'IDX_email_fallback_unique');
    }

    public function down(schema $schema)
    {
        $schema->droptable('email_fallback');

    }
}
