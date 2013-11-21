<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131121181046 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("DROP INDEX filter_questionnaire_usage_unique");
        $this->addSql("CREATE UNIQUE INDEX filter_questionnaire_usage_unique ON filter_questionnaire_usage (filter_id, questionnaire_id, part_id, rule_id, is_second_level)");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
