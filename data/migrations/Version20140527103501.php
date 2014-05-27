<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140527103501 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("UPDATE role SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE \"user\" SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE permission SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE filter_questionnaire_usage SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE rule SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE filter_geoname_usage SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE questionnaire_usage SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE part SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE population SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE answer SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE country SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE questionnaire SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE user_survey SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE survey SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE user_filter_set SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE geoname SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE user_questionnaire SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE note SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE filter SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE filter_set SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE question SET date_created = COALESCE(date_created, date_modified, NOW())");
        $this->addSql("UPDATE choice SET date_created = COALESCE(date_created, date_modified, NOW())");

        $this->addSql("ALTER TABLE role ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE role ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE \"user\" ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE \"user\" ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE permission ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE permission ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE filter_questionnaire_usage ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE filter_questionnaire_usage ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE rule ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE rule ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE filter_geoname_usage ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE filter_geoname_usage ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE questionnaire_usage ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE questionnaire_usage ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE part ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE part ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE population ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE population ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE answer ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE answer ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE country ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE country ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE questionnaire ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE questionnaire ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE user_survey ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE user_survey ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE survey ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE survey ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE user_filter_set ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE user_filter_set ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE geoname ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE geoname ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE user_questionnaire ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE user_questionnaire ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE note ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE note ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE filter ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE filter ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE filter_set ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE filter_set ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE question ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE question ALTER date_created SET NOT NULL");
        $this->addSql("ALTER TABLE choice ALTER date_created SET DEFAULT 'NOW()'");
        $this->addSql("ALTER TABLE choice ALTER date_created SET NOT NULL");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
