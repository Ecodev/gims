<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130419180133 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE population DROP CONSTRAINT FK_B449A008F92F3E70;");
        $this->addSql("DROP INDEX IDX_B449A008F92F3E70");
        $this->addSql("DROP INDEX population_unique");
        $this->addSql("ALTER TABLE population RENAME country_id TO old_country_id;");


        $this->addSql("ALTER TABLE country ADD creator_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE country ADD modifier_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE country ADD date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL");
        $this->addSql("ALTER TABLE country ADD date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL");
        $this->addSql("ALTER TABLE country RENAME id TO code");
        $this->addSql("DROP SEQUENCE country_id_seq CASCADE;");
        $this->addSql("ALTER TABLE country ADD id SERIAL NOT NULL");
        $this->addSql("ALTER TABLE country ADD CONSTRAINT FK_5373C96661220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE country ADD CONSTRAINT FK_5373C966D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_5373C96661220EA6 ON country (creator_id)");
        $this->addSql("CREATE INDEX IDX_5373C966D079F553 ON country (modifier_id)");
        $this->addSql("CREATE UNIQUE INDEX country_code_unique ON country (code)");
        $this->addSql("ALTER TABLE country DROP CONSTRAINT country_pkey;");
        $this->addSql("ALTER TABLE country ADD PRIMARY KEY (id);");


        $this->addSql("ALTER TABLE population ADD country_id INT NOT NULL");
        $this->addSql("UPDATE population SET country_id = country.id FROM country WHERE country.code = population.old_country_id");
        $this->addSql("ALTER TABLE population ALTER COLUMN id SET NOT NULL");
        $this->addSql("ALTER TABLE population DROP old_country_id");
        $this->addSql("CREATE INDEX IDX_B449A008F92F3E70 ON population (country_id)");
        $this->addSql("CREATE UNIQUE INDEX population_unique ON population (year, country_id, part_id)");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A008F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");


        $this->addSql("CREATE UNIQUE INDEX survey_code_unique ON survey (code)");
        $this->addSql("ALTER TABLE geoname ADD creator_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE geoname ADD modifier_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE geoname ADD date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL");
        $this->addSql("ALTER TABLE geoname ADD date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL");
        $this->addSql("SELECT setval('geoname_id_seq', (SELECT MAX(id) FROM geoname))");
        $this->addSql("ALTER TABLE geoname ALTER id SET DEFAULT nextval('geoname_id_seq')");
        $this->addSql("ALTER TABLE geoname ADD CONSTRAINT FK_EF41727A61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE geoname ADD CONSTRAINT FK_EF41727AD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_EF41727A61220EA6 ON geoname (creator_id)");
        $this->addSql("CREATE INDEX IDX_EF41727AD079F553 ON geoname (modifier_id)");
        $this->addSql("DROP TABLE setting;");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
