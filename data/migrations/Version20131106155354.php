<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131106155354 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE TABLE note (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, question_id INT DEFAULT NULL, questionnaire_id INT DEFAULT NULL, survey_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, description VARCHAR(1023) DEFAULT NULL, attachment_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_CFBDFA1461220EA6 ON note (creator_id)");
        $this->addSql("CREATE INDEX IDX_CFBDFA14D079F553 ON note (modifier_id)");
        $this->addSql("CREATE INDEX IDX_CFBDFA141E27F6BF ON note (question_id)");
        $this->addSql("CREATE INDEX IDX_CFBDFA14CE07E8FF ON note (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_CFBDFA14B3FE509D ON note (survey_id)");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1461220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA141E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE");

        $this->addSql("ALTER TABLE \"user\" ADD country_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE \"user\" ADD phone VARCHAR(25) DEFAULT NULL");
        $this->addSql("ALTER TABLE \"user\" ADD skype VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE \"user\" ADD job VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE \"user\" ADD ministry VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE \"user\" ADD address VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE \"user\" ADD zip VARCHAR(10) DEFAULT NULL");
        $this->addSql("ALTER TABLE \"user\" ADD city VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE \"user\" ADD last_login TIMESTAMP(0) WITH TIME ZONE");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT FK_8D93D649F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_8D93D649F92F3E70 ON \"user\" (country_id)");

        $this->addSql("INSERT INTO permission (name) VALUES ('Note-create')");
        $this->addSql("INSERT INTO permission (name) VALUES ('Note-update')");
        $this->addSql("INSERT INTO permission (name) VALUES ('Note-read')");
        $this->addSql("INSERT INTO permission (name) VALUES ('Note-delete')");

        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (1, 55) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (2, 55) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 53) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 54) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 55) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (3, 56) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (4, 53) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (4, 54) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (4, 55) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (4, 56) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (5, 53) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (5, 54) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (5, 55) ");
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) VALUES (5, 56) ");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
