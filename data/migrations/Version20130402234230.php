<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130402234230 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE population ALTER country_id SET NOT NULL");
        $this->addSql("ALTER TABLE answer ALTER questionnaire_id SET NOT NULL");
        $this->addSql("ALTER TABLE answer ALTER question_id SET NOT NULL");
        $this->addSql("ALTER TABLE questionnaire ALTER geoname_id SET NOT NULL");
        $this->addSql("ALTER TABLE user_survey ALTER user_id SET NOT NULL");
        $this->addSql("ALTER TABLE user_survey ALTER role_id SET NOT NULL");
        $this->addSql("ALTER TABLE user_survey ALTER survey_id SET NOT NULL");
        $this->addSql("ALTER TABLE user_questionnaire ALTER user_id SET NOT NULL");
        $this->addSql("ALTER TABLE user_questionnaire ALTER questionnaire_id SET NOT NULL");
        $this->addSql("ALTER TABLE user_questionnaire ALTER role_id SET NOT NULL");
        $this->addSql("ALTER TABLE question ALTER category_id SET NOT NULL");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
