<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140123164137 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // Inject geonames which are not 'PCLI' but are still used to represent some countries (directly imported from official geonames DB)
        $this->addSql("INSERT INTO geoname (id, name, asciiname, alternatenames, latitude, longitude, fclass, fcode, country_code, cc2, admin1, admin2, admin3, admin4, population, elevation, gtopo30, timezone, moddate, geometry) VALUES (8505032, 'Netherlands Antilles', 'Netherlands Antilles', 'Antia Hulandes,Dutch Antilles,Nederlandse Antillen,Netherlands Antilles', 12.11721, -68.9196400000000011, 'A', 'PCLH', 'CW', NULL, '00', NULL, NULL, NULL, 175653, NULL, 1, 'America/Curacao', '2014-01-04', '0101000020E6100000FB05BB61DB3A51C04087F9F2023C2840');");
        $this->addSql("INSERT INTO geoname (id, name, asciiname, alternatenames, latitude, longitude, fclass, fcode, country_code, cc2, admin1, admin2, admin3, admin4, population, elevation, gtopo30, timezone, moddate, geometry) VALUES (8505033, 'Serbia and Montenegro', 'Serbia and Montenegro', 'CS,Federal Republic of Yugoslavia,SCG,Serbia and Montenegro,State Union of Serbia and Montenegro', 44.8173999999999992, 20.4634099999999997, 'A', 'PCLH', 'RS', NULL, '00', NULL, NULL, NULL, 0, NULL, 110, 'Europe/Belgrade', '2013-04-02', '0101000020E6100000ADA3AA09A27634400DE02D90A0684640');");

        // Link the countries with new geonames
        $this->addSql("UPDATE country SET geoname_id = 8505032 WHERE iso3 = 'ANT'");
        $this->addSql("UPDATE country SET geoname_id = 8505033 WHERE iso3 = 'SCG'");

        // Fix the relation country-geoname, which used to have a double foreign key in both tables
        $this->addSql("ALTER TABLE country ALTER geoname_id SET NOT NULL;");
        $this->addSql("ALTER TABLE geoname DROP country_id;");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
