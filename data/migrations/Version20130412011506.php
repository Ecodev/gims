<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130412011506 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE TYPE questionnaire_status AS ENUM ('new', 'completed', 'validated', 'rejected');");
        $this->addSql("ALTER TABLE answer DROP status");
        
        $this->addSql("ALTER TABLE questionnaire ADD status questionnaire_status");
        $this->addSql("UPDATE questionnaire SET status = 'new'");
        $this->addSql("ALTER TABLE questionnaire ALTER COLUMN status SET NOT NULL");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
