<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130906020231 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // Give the role of 'editor' for all survey creator, but only if it doesn't already have the role (and if survey has a creator)
        $this->addSql("INSERT INTO user_survey (user_id, survey_id, role_id, date_created)
SELECT survey.creator_id, survey.id, role.id, NOW()
FROM survey
CROSS JOIN role
LEFT JOIN user_survey AS existing ON (existing.user_id = survey.creator_id AND existing.survey_id = survey.id AND existing.role_id = role.id)
WHERE role.name = 'editor'
AND survey.creator_id IS NOT NULL
AND existing.user_id IS NULL");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
