<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141020221020 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // Copy iso3 from country to geoname
        $this->addSql("ALTER TABLE geoname ADD iso3 VARCHAR(255) DEFAULT NULL");
        $this->addSql('UPDATE geoname SET iso3 = country.iso3 FROM country WHERE geoname.id = country.geoname_id;');

        $this->addSql("ALTER TABLE population DROP CONSTRAINT fk_b449a008f92f3e70");
        $this->addSql("ALTER TABLE \"user\" DROP CONSTRAINT fk_8d93d649f92f3e70");
        $this->addSql("DROP SEQUENCE country_id_seq CASCADE");
        $this->addSql("DROP INDEX idx_8d93d649f92f3e70");

        // Transform user.country_id values into user.geoname_id
        $this->addSql('UPDATE "user" SET country_id = geoname.id FROM geoname JOIN country ON (country.geoname_id = geoname.id) WHERE "user".country_id = country.id;');

        $this->addSql("ALTER TABLE \"user\" RENAME COLUMN country_id TO geoname_id");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT FK_8D93D64923F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_8D93D64923F5422B ON \"user\" (geoname_id)");
        $this->addSql("DROP INDEX idx_b449a008f92f3e70");
        $this->addSql("DROP INDEX population_unique_official");
        $this->addSql("DROP INDEX population_unique_non_official");

        // Transform population.country_id values into population.geoname_id
        $this->addSql('UPDATE population SET country_id = geoname.id FROM geoname JOIN country ON (country.geoname_id = geoname.id) WHERE population.country_id = country.id;');

        $this->addSql("ALTER TABLE population RENAME COLUMN country_id TO geoname_id");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A00823F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_B449A00823F5422B ON population (geoname_id)");
        $this->addSql("CREATE UNIQUE INDEX population_unique_official ON population (year, geoname_id, part_id) WHERE questionnaire_id IS NULL");
        $this->addSql("CREATE UNIQUE INDEX population_unique_non_official ON population (year, geoname_id, part_id, questionnaire_id) WHERE questionnaire_id IS NOT NULL");
        $this->addSql("DROP TABLE country");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
