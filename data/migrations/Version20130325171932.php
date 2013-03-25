<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130325171932 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE population ADD part_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE population ADD population INT NOT NULL");
        $this->addSql("ALTER TABLE population DROP urban");
        $this->addSql("ALTER TABLE population DROP rural");
        $this->addSql("ALTER TABLE population DROP total");
        $this->addSql("DROP INDEX population_unique");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A0084CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_B449A0084CE34BEC ON population (part_id)");
        $this->addSql("CREATE UNIQUE INDEX population_unique ON population (year, country_id, part_id)");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
