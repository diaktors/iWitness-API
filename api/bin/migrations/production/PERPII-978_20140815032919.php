<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\ApiMigration;
use Doctrine\DBAL\Schema\Schema;

/**
* Auto-generated Migration: Please modify to your needs!
*/
class PERPII_978_20140815032919 extends ApiMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
{

    //new iOS key
    $this->addSql(" INSERT INTO `oauth_clients` (
                        `client_id`,
                        `client_secret`,
                        `redirect_uri`,
                        `grant_types`,
                        `scope`,
                        `user_id` )
                    VALUES (
                      '1ef28784-23f8-11e4-b8aa-000c29c9a052',
                      '26b30aba-23f8-11e4-b8aa-000c29c9a052',
                      '',
                      'password refresh_token',
                      NULL,
                      NULL)"
    );
    //android
    $this->addSql("UPDATE oauth_clients
                   SET client_secret = 'a81af5fc-23f0-11e4-b8aa-000c29c9a052'
                   WHERE client_id='ba5659b4-f5a1-11e3-bc94-000c29c9a052' "
    );

    //web
    $this->addSql("UPDATE oauth_clients
                   SET client_secret = 'ad990419-23f0-11e4-b8aa-000c29c9a052'
                   WHERE client_id='e114cbaa-f5a1-11e3-bc94-000c29c9a052' "
    );

}

public function down(Schema $schema)
{
    $this->addSql("DELETE FROM  `oauth_clients`  WHERE client_id='1ef28784-23f8-11e4-b8aa-000c29c9a052' ");
    //android
    $this->addSql("UPDATE oauth_clients SET client_secret = '' WHERE client_id='ba5659b4-f5a1-11e3-bc94-000c29c9a052' ");
    //web
    $this->addSql("UPDATE oauth_clients SET client_secret = '' WHERE client_id='e114cbaa-f5a1-11e3-bc94-000c29c9a052' ");

    }
}
