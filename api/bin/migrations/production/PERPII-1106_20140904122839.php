<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_1106_20140904122839 extends ApiMigration
{
    public function up(Schema $schema)
    {

        $sql = "
INSERT INTO `plan` (`id`, `key`, `name`, `description`, `price`, `length`, `flags`, `created`, `modified`, `deleted`)
VALUES
	(UUID_TO_BIN('7dfbfff7-342f-11e4-9c22-000c29c9a052'), 'wspta', 'wspta', 'Washington State PTA special price', 14.95, 12, 0, UNIX_TIMESTAMP(), NULL, NULL)
";
        $this->addSql($sql);

    }

    public function down(Schema $schema)
    {
        $ids = array(
            '7dfbfff7-342f-11e4-9c22-000c29c9a052',
        );

        foreach ($ids as $id) {
            $sql = "DELETE FROM `plan` WHERE id = `UUID_TO_BIN`('" . $id . "')";
            $this->addSql($sql);
        }
    }
}
