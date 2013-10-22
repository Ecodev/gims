<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131022164739 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // Import real data from geoname for 'Channel Islands'
        $this->addSql("INSERT INTO geoname (id, name, asciiname, alternatenames, latitude, longitude, fclass, fcode, country, cc2, admin1, admin2, admin3, admin4, population, elevation, gtopo30, timezone, moddate, geometry) VALUES (3042400, 'Channel Islands', 'Channel Islands', 'Channel Islands,channel', 49.3466000000000022, -2.36206000000000005, 'T', 'ISLS', 'GG', 'GB', '00', NULL, NULL, NULL, 158000, NULL, -9999, 'Europe/Guernsey', '2010-03-02', '0101000020E6100000A18499B67FE502C0956588635DAC4840');");

        // Import made-up data to create a fake country 'Channel Islands', as seen in JMP country files and population sources
        $this->addSql("INSERT INTO country (geoname_id, code, iso3, iso_numeric, name, area) VALUES (3042400, 'ZZ', 'CIS', 830, 'Channel Islands', NULL);");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
