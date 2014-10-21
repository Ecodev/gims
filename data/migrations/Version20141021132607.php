<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141021132607 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE geoname DROP asciiname");
        $this->addSql("ALTER TABLE geoname DROP alternatenames");
        $this->addSql("ALTER TABLE geoname DROP latitude");
        $this->addSql("ALTER TABLE geoname DROP longitude");
        $this->addSql("ALTER TABLE geoname DROP fclass");
        $this->addSql("ALTER TABLE geoname DROP fcode");
        $this->addSql("ALTER TABLE geoname DROP country_code");
        $this->addSql("ALTER TABLE geoname DROP cc2");
        $this->addSql("ALTER TABLE geoname DROP admin1");
        $this->addSql("ALTER TABLE geoname DROP admin2");
        $this->addSql("ALTER TABLE geoname DROP admin3");
        $this->addSql("ALTER TABLE geoname DROP admin4");
        $this->addSql("ALTER TABLE geoname DROP population");
        $this->addSql("ALTER TABLE geoname DROP elevation");
        $this->addSql("ALTER TABLE geoname DROP gtopo30");
        $this->addSql("ALTER TABLE geoname DROP timezone");
        $this->addSql("ALTER TABLE geoname DROP moddate");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
