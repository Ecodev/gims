<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130322122116 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE SEQUENCE permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE TABLE role_permission (role_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(role_id, permission_id))");
        $this->addSql("CREATE INDEX IDX_6F7DF886D60322AC ON role_permission (role_id)");
        $this->addSql("CREATE INDEX IDX_6F7DF886FED90CCA ON role_permission (permission_id)");
        $this->addSql("CREATE TABLE permission (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_E04992AA61220EA6 ON permission (creator_id)");
        $this->addSql("CREATE INDEX IDX_E04992AAD079F553 ON permission (modifier_id)");
        $this->addSql("CREATE UNIQUE INDEX permission_unique ON permission (name)");
        $this->addSql("ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE permission ADD CONSTRAINT FK_E04992AA61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE permission ADD CONSTRAINT FK_E04992AAD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ALTER survey_id SET NOT NULL");
        $this->addSql("CREATE UNIQUE INDEX user_survey_unique ON user_survey (user_id, survey_id, role_id)");
        $this->addSql("ALTER TABLE role ADD parent_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE role DROP can_validate_questionnaire");
        $this->addSql("ALTER TABLE role DROP can_link_official_question");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT FK_57698A6A727ACA70 FOREIGN KEY (parent_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_57698A6A727ACA70 ON role (parent_id)");
        $this->addSql("CREATE UNIQUE INDEX role_unique ON role (name)");
        $this->addSql("CREATE UNIQUE INDEX user_questionnaire_unique ON user_questionnaire (user_id, questionnaire_id, role_id)");


        $this->addSql("INSERT INTO role (id, name, date_created) VALUES (nextval('role_id_seq'), 'anonymous', NOW())");
        $this->addSql("INSERT INTO role (id, name, date_created) VALUES (nextval('role_id_seq'), 'member', NOW())");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
