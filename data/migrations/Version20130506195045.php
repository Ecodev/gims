<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130506195045 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE survey ADD comments TEXT DEFAULT NULL");
        $this->addSql("ALTER TABLE survey ADD date_started TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL");
        $this->addSql("ALTER TABLE survey ADD date_ended TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
