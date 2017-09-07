<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class PERPII_996_20140821045155 extends ApiMigration
{
    public function up(Schema $schema)
    {
        /**
         * Google receiptId template:
         * 12999556515565155651.5565135565155651 (base order number)
         * 12999556515565155651.5565135565155651..0 (initial purchase orderID)
         * 12999556515565155651.5565135565155651..1 (first recurrence orderID)
         * 12999556515565155651.5565135565155651..2 (second recurrence orderID)
         */
        $schema
            ->getTable('subscription')
            ->getColumn('receipt_id')
            ->setlength(40); //base order number
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema
            ->getTable('subscription')
            ->getColumn('receipt_id')
            ->setlength(16);
    }
}
