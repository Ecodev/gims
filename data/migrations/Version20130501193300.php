<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130501193300 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE rule ADD filter_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE rule ADD ratio NUMERIC(4, 3) DEFAULT NULL");
        $this->addSql("ALTER TABLE rule ADD CONSTRAINT FK_46D8ACCCD395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_46D8ACCCD395B25E ON rule (filter_id)");
        $this->addSql("ALTER TABLE rule ADD CHECK (((ratio >= (0)::numeric) AND (ratio <= (1)::numeric)));");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
