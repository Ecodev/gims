<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130312164308 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE SEQUENCE population_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE TABLE population (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, country_id VARCHAR(255) DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, year INT NOT NULL, urban INT NOT NULL, rural INT NOT NULL, total INT NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_B449A00861220EA6 ON population (creator_id)");
        $this->addSql("CREATE INDEX IDX_B449A008D079F553 ON population (modifier_id)");
        $this->addSql("CREATE INDEX IDX_B449A008F92F3E70 ON population (country_id)");
        $this->addSql("CREATE UNIQUE INDEX population_unique ON population (year, country_id)");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A00861220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A008D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A008F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE country DROP population");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("DROP SEQUENCE population_id_seq");
        $this->addSql("DROP TABLE population");
        $this->addSql("ALTER TABLE country ADD population INT DEFAULT NULL");
    }
}
