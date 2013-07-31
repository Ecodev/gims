<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130731141445 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE TABLE questionnaire_rule (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, questionnaire_id INT NOT NULL, part_id INT DEFAULT NULL, rule_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, justification VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_1240E36561220EA6 ON questionnaire_rule (creator_id)");
        $this->addSql("CREATE INDEX IDX_1240E365D079F553 ON questionnaire_rule (modifier_id)");
        $this->addSql("CREATE INDEX IDX_1240E365CE07E8FF ON questionnaire_rule (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_1240E3654CE34BEC ON questionnaire_rule (part_id)");
        $this->addSql("CREATE INDEX IDX_1240E365744E0351 ON questionnaire_rule (rule_id)");
        $this->addSql("CREATE UNIQUE INDEX questionnaire_rule_unique ON questionnaire_rule (questionnaire_id, part_id, rule_id)");
        $this->addSql("ALTER TABLE questionnaire_rule ADD CONSTRAINT FK_1240E36561220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_rule ADD CONSTRAINT FK_1240E365D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_rule ADD CONSTRAINT FK_1240E365CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_rule ADD CONSTRAINT FK_1240E3654CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_rule ADD CONSTRAINT FK_1240E365744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE SEQUENCE filter_rule_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE rule_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE part_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE population_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE country_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE survey_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE questionnaire_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE user_survey_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE geoname_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE user_questionnaire_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE filter_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE filter_set_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE question_choice_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE question_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("DROP TABLE questionnaire_rule");
    }
}
