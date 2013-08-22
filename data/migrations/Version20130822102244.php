<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130822102244 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE question ADD is_final BOOLEAN DEFAULT 'false'");
        $this->addSql("ALTER TABLE question DROP final");

        $this->addSql("ALTER TABLE question ADD is_multiple BOOLEAN DEFAULT 'false'");
        $this->addSql("ALTER TABLE question DROP multiple");

        $this->addSql("ALTER TABLE question ADD is_compulsory BOOLEAN DEFAULT 'true'");
        $this->addSql("ALTER TABLE question DROP compulsory");

        $this->addSql("ALTER TABLE survey RENAME COLUMN active TO is_active");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
