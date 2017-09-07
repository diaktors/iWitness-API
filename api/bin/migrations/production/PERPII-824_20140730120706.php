<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_824_20140730120706 extends ApiMigration
{
    public function up(Schema $schema)
    {

        $sql = "
INSERT INTO `plan` (`id`, `key`, `name`, `description`, `price`, `length`, `flags`, `created`, `modified`, `deleted`)
VALUES
	(UUID_TO_BIN('4ae611ed-17a5-11e4-b8aa-000c29c9a052'), '2-years', '2-years', 'Two Year subscription', 49.99, 24, 0, UNIX_TIMESTAMP(), NULL, NULL),
	(UUID_TO_BIN('512a025d-17a5-11e4-b8aa-000c29c9a052'), 'seattleyear', 'seattleyear', 'Special boys & girls clubs pricing one year (Per Person) subscription', 14.95, 12, 0, UNIX_TIMESTAMP(), NULL, NULL),
	(UUID_TO_BIN('57101646-17a5-11e4-b8aa-000c29c9a052'), 'year', 'year', 'One year subscription', 29.99, 12, 0, UNIX_TIMESTAMP(), NULL, NULL),
	(UUID_TO_BIN('5cdf310d-17a5-11e4-b8aa-000c29c9a052'), 'safekidyear', 'safekidyear', 'Special seattle university pricing one year (Per Person) subscription', 19.95, 12, 0, UNIX_TIMESTAMP(), NULL, NULL),
	(UUID_TO_BIN('61648d23-17a5-11e4-b8aa-000c29c9a052'), 'giftplanyear', 'giftplanyear', 'One year gift card subscription', 29.99, 12, 0, UNIX_TIMESTAMP(), NULL, NULL),
	(UUID_TO_BIN('67dd4e88-17a5-11e4-b8aa-000c29c9a052'), 'free', 'free', 'free plan', 0, 0, 0, UNIX_TIMESTAMP(), NULL, NULL),
	(UUID_TO_BIN('6cec2f20-17a5-11e4-b8aa-000c29c9a052'), 'month', 'month', 'Per month', 2.99, 1, 0, UNIX_TIMESTAMP(), NULL, NULL);
";
        $this->addSql($sql);

    }

    public function down(Schema $schema)
    {
        $ids = array(
            '4ae611ed-17a5-11e4-b8aa-000c29c9a052',
            '512a025d-17a5-11e4-b8aa-000c29c9a052',
            '57101646-17a5-11e4-b8aa-000c29c9a052',
            '5cdf310d-17a5-11e4-b8aa-000c29c9a052',
            '61648d23-17a5-11e4-b8aa-000c29c9a052',
            '67dd4e88-17a5-11e4-b8aa-000c29c9a052',
            '6cec2f20-17a5-11e4-b8aa-000c29c9a052',
        );

        foreach ($ids as $id) {
            $sql = "delete from `plan` where id = `UUID_TO_BIN`('" . $id . "')";
            $this->addSql($sql);
        }
    }
}
