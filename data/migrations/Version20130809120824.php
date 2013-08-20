<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130809120824 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("TRUNCATE part CASCADE");
        $this->addSql("TRUNCATE filter_rule CASCADE");
        $this->addSql("TRUNCATE question CASCADE");
        $this->addSql("ALTER TABLE filter_rule ALTER part_id SET NOT NULL");
        $this->addSql("ALTER TABLE questionnaire_rule ALTER part_id SET NOT NULL");
        $this->addSql("ALTER TABLE part ADD is_total BOOLEAN NOT NULL");
        $this->addSql("ALTER TABLE population ALTER part_id SET NOT NULL");
        $this->addSql("ALTER TABLE population DROP CONSTRAINT FK_B449A0084CE34BEC");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A0084CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ALTER part_id SET NOT NULL");
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT FK_DADD4A254CE34BEC");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A254CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");

        $this->addSql("INSERT INTO part (date_created, date_modified, name, is_total) VALUES (NOW(), NOW(), 'Urban', false);");
        $this->addSql("INSERT INTO part (date_created, date_modified, name, is_total) VALUES (NOW(), NOW(), 'Rural', false);");
        $this->addSql("INSERT INTO part (date_created, date_modified, name, is_total) VALUES (NOW(), NOW(), 'Total', true);");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}