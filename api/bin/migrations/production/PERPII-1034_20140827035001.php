<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_1034_20140827035001 extends ApiMigration
{
    public function up(Schema $schema)
    {
        $contact = $schema->getTable('user');

        if ($contact->hasIndex('IDX_user_phone')) {
            $contact->dropIndex('IDX_user_phone');
        }
        if ($contact->hasIndex('IDX_user_email')) {
            $contact->dropIndex('IDX_user_email');
        }

        if (!$contact->hasIndex('IDX_user_email_deleted')) {
            $contact->addUniqueIndex(array('email', 'type', 'deleted'), 'IDX_user_email_deleted');
        }

        if (!$contact->hasIndex('IDX_user_phone_deleted')) {
            $contact->addUniqueIndex(array('phone', 'type', 'deleted'), 'IDX_user_phone_deleted');
        }
    }

    public function down(Schema $schema)
    {
        $contact = $schema->getTable('user');
        if ($contact->hasIndex('IDX_user_phone_deleted')) {
            $contact->dropIndex('IDX_user_phone_deleted');
        }
        if ($contact->hasIndex('IDX_user_email_deleted')) {
            $contact->dropIndex('IDX_user_email_deleted');
        }

        if (!$contact->hasIndex('IDX_user_email')) {
            $contact->addUniqueIndex(array('email', 'type'), 'IDX_user_email');
        }

        if (!$contact->hasIndex('IDX_user_phone')) {
            $contact->addUniqueIndex(array('phone', 'type'), 'IDX_user_phone');
        }
    }
}
