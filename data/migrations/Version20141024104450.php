<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141024104450 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE questionnaire_usage ADD thematic_filter_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD73A7D75635 FOREIGN KEY (thematic_filter_id) REFERENCES filter (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_7FB5DD73A7D75635 ON questionnaire_usage (thematic_filter_id)");
        $this->addSql("DELETE FROM population WHERE year < 1980 OR year > 2020");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
