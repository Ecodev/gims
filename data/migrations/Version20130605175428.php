<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130605175428 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE rule ADD name VARCHAR(255)");
        $this->addSql("UPDATE rule SET name = 'database migration default name'");
        $this->addSql("ALTER TABLE rule ALTER COLUMN name SET NOT NULL");

        $this->addSql("ALTER TABLE rule ADD formula VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE rule ADD value NUMERIC(4, 3) DEFAULT NULL");
        $this->addSql("ALTER TABLE rule ADD CHECK (((value >= (-1)::numeric) AND (value <= (1)::numeric)));");


        // Add justification field
        $this->addSql("ALTER TABLE filter_rule ADD justification VARCHAR(255)");
        $this->addSql("UPDATE filter_rule SET justification = 'Imported from country files'");
        $this->addSql("ALTER TABLE filter_rule ALTER COLUMN justification SET NOT NULL");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
