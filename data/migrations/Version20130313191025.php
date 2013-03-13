<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130313191025 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE answer ALTER COLUMN value_percent TYPE numeric(3,2);");
        $this->addSql("ALTER TABLE answer ALTER COLUMN quality TYPE numeric(3,2);");
        $this->addSql("ALTER TABLE answer ALTER COLUMN relevance TYPE numeric(3,2);");
        $this->addSql("ALTER TABLE population ALTER year TYPE NUMERIC(4, 0);");
        $this->addSql("ALTER TABLE survey ALTER year TYPE NUMERIC(4, 0);");
        
        $this->addSql("ALTER TABLE answer ADD CHECK (((value_percent >= (0)::numeric) AND (value_percent <= (1)::numeric)));");
        $this->addSql("ALTER TABLE answer ADD CHECK (((quality >= (0)::numeric) AND (quality <= (1)::numeric)));");
        $this->addSql("ALTER TABLE answer ADD CHECK (((relevance >= (0)::numeric) AND (relevance <= (1)::numeric)));");
        $this->addSql("ALTER TABLE population ADD CHECK (((year >= (1900)::numeric) AND (year <= (3000)::numeric)));");
        $this->addSql("ALTER TABLE survey ADD CHECK (((year >= (1900)::numeric) AND (year <= (3000)::numeric)));");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->throwIrreversibleMigrationException();
    }

}
