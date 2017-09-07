<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_919_20140808081716 extends ApiMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        //contact
        $contact = $schema->getTable('contact');

        if ($contact->hasIndex('IDX_user_phone')) {
            $contact->dropIndex('IDX_user_phone');
        }
        if ($contact->hasIndex('IDX_user_email')) {
            $contact->dropIndex('IDX_user_email');
        }

        if (!$contact->hasIndex('IDX_user_email_deleted')) {
            $contact->addUniqueIndex(array('email', 'user_id', 'deleted'), 'IDX_user_email_deleted');
        }

        if (!$contact->hasIndex('IDX_user_phone_deleted')) {
            $contact->addUniqueIndex(array('phone', 'user_id', 'deleted'), 'IDX_user_phone_deleted');
        }
    }

    public function down(Schema $schema)
    {
        //contact
        $contact = $schema->getTable('contact');
        if ($contact->hasIndex('IDX_user_phone_deleted')) {
            $contact->dropIndex('IDX_user_phone_deleted');
        }
        if ($contact->hasIndex('IDX_user_email_deleted')) {
            $contact->dropIndex('IDX_user_email_deleted');
        }

        if (!$contact->hasIndex('IDX_user_email')) {
            $contact->addUniqueIndex(array('email', 'user_id'), 'IDX_user_email');
        }

        if (!$contact->hasIndex('IDX_user_phone')) {
            $contact->addUniqueIndex(array('phone', 'user_id'), 'IDX_user_phone');
        }
    }
}
