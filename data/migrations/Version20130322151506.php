<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130322151506 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("SELECT setval('population_id_seq', (SELECT MAX(id) FROM population))");
        $this->addSql("ALTER TABLE population ALTER id SET DEFAULT nextval('population_id_seq')");
        $this->addSql("SELECT setval('answer_id_seq', (SELECT MAX(id) FROM answer))");
        $this->addSql("ALTER TABLE answer ALTER id SET DEFAULT nextval('answer_id_seq')");
        $this->addSql("SELECT setval('questionnaire_id_seq', (SELECT MAX(id) FROM questionnaire))");
        $this->addSql("ALTER TABLE questionnaire ALTER id SET DEFAULT nextval('questionnaire_id_seq')");
        $this->addSql("SELECT setval('user_survey_id_seq', (SELECT MAX(id) FROM user_survey))");
        $this->addSql("ALTER TABLE user_survey ALTER id SET DEFAULT nextval('user_survey_id_seq')");
        $this->addSql("SELECT setval('role_id_seq', (SELECT MAX(id) FROM role))");
        $this->addSql("ALTER TABLE role ALTER id SET DEFAULT nextval('role_id_seq')");
        $this->addSql("SELECT setval('survey_id_seq', (SELECT MAX(id) FROM survey))");
        $this->addSql("ALTER TABLE survey ALTER id SET DEFAULT nextval('survey_id_seq')");
        $this->addSql("SELECT setval('permission_id_seq', (SELECT MAX(id) FROM permission))");
        $this->addSql("ALTER TABLE permission ALTER id SET DEFAULT nextval('permission_id_seq')");
        $this->addSql("SELECT setval('category_id_seq', (SELECT MAX(id) FROM category))");
        $this->addSql("ALTER TABLE category ALTER id SET DEFAULT nextval('category_id_seq')");
        $this->addSql("SELECT setval('user_id_seq', (SELECT MAX(id) FROM \"user\"))");
        $this->addSql("ALTER TABLE \"user\" ALTER id SET DEFAULT nextval('user_id_seq')");
        $this->addSql("SELECT setval('user_questionnaire_id_seq', (SELECT MAX(id) FROM user_questionnaire))");
        $this->addSql("ALTER TABLE user_questionnaire ALTER id SET DEFAULT nextval('user_questionnaire_id_seq')");
        $this->addSql("SELECT setval('question_id_seq', (SELECT MAX(id) FROM question))");
        $this->addSql("ALTER TABLE question ALTER id SET DEFAULT nextval('question_id_seq')");

        // Questions are linked to survey, not to questionnaire
        $this->addSql("ALTER TABLE question DROP CONSTRAINT fk_b6f7494ece07e8ff");
        $this->addSql("DROP INDEX idx_b6f7494ece07e8ff");
        $this->addSql("ALTER TABLE question DROP questionnaire_id");
        $this->addSql("ALTER TABLE question ADD survey_id INT NOT NULL");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494EB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_B6F7494EB3FE509D ON question (survey_id)");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
