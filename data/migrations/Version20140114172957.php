<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140114172957 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // Democratic Republic of the Congo had wrong latitude and longitude data, leading to wrong geometry, leading to crash when unserializing from DB
        $this->addSql('UPDATE "geoname" SET "latitude" = -2.5, "longitude" = 23.5, geometry = st_makepoint(23.5, -2.5)');
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
