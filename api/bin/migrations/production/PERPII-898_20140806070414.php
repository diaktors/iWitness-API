<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_898_20140806070414 extends ApiMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        //user
        $user = $schema->getTable('user');
        $user->getColumn('phone')->setNotnull(false)->setDefault(null);
        $user = $schema->getTable('user');
        if (!$user->hasIndex('IDX_user_phone')) {
            $user->addUniqueIndex(array('phone', 'type'), 'IDX_user_phone');
        }
        if (!$user->hasIndex('IDX_user_email')) {
            $user->addUniqueIndex(array('email', 'type'), 'IDX_user_email');
        }

        //contact
        $contact = $schema->getTable('contact');
        if (!$contact->hasIndex('IDX_user_phone')) {
            $contact->addUniqueIndex(array('phone', 'user_id'), 'IDX_user_phone');
        }
        if (!$contact->hasIndex('IDX_user_email')) {
            $contact->addUniqueIndex(array('email', 'user_id'), 'IDX_user_email');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $user = $schema->getTable('user');
        $user->getColumn('phone')->setNotnull(true);
        if ($user->hasIndex('IDX_user_phone')) {
            $user->dropIndex('IDX_user_phone');
        }
        if ($user->hasIndex('IDX_user_email')) {
            $user->dropIndex('IDX_user_email');
        }

        //contact
        $contact = $schema->getTable('contact');
        if ($contact->hasIndex('IDX_user_phone')) {
            $contact->dropIndex('IDX_user_phone');
        }
        if ($contact->hasIndex('IDX_user_email')) {
            $contact->dropIndex('IDX_user_email');
        }
    }
}
