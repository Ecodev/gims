<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130807145506 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE question_choice ADD sorting SMALLINT DEFAULT '0' NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE SEQUENCE question_choice_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE filter_rule_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE rule_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE part_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE population_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE country_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE questionnaire_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE user_survey_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE survey_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE geoname_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE user_questionnaire_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE filter_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE filter_set_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE question_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("ALTER TABLE question_choice DROP sorting");
    }
}
