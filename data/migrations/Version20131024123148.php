<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131024123148 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("DROP SEQUENCE filter_rule_id_seq CASCADE");
        $this->addSql("DROP SEQUENCE questionnaire_formula_id_seq CASCADE");
        $this->addSql("DROP SEQUENCE filter_formula_id_seq CASCADE");
        $this->addSql("CREATE TABLE filter_questionnaire_usage (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, rule_id INT NOT NULL, part_id INT NOT NULL, questionnaire_id INT NOT NULL, filter_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, justification VARCHAR(255) NOT NULL, sorting SMALLINT DEFAULT '0' NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_F5F3A4A661220EA6 ON filter_questionnaire_usage (creator_id)");
        $this->addSql("CREATE INDEX IDX_F5F3A4A6D079F553 ON filter_questionnaire_usage (modifier_id)");
        $this->addSql("CREATE INDEX IDX_F5F3A4A6744E0351 ON filter_questionnaire_usage (rule_id)");
        $this->addSql("CREATE INDEX IDX_F5F3A4A64CE34BEC ON filter_questionnaire_usage (part_id)");
        $this->addSql("CREATE INDEX IDX_F5F3A4A6CE07E8FF ON filter_questionnaire_usage (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_F5F3A4A6D395B25E ON filter_questionnaire_usage (filter_id)");
        $this->addSql("CREATE UNIQUE INDEX filter_questionnaire_usage_unique ON filter_questionnaire_usage (filter_id, questionnaire_id, part_id, rule_id)");
        $this->addSql("CREATE TABLE questionnaire_usage (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, rule_id INT NOT NULL, part_id INT NOT NULL, questionnaire_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, justification VARCHAR(255) NOT NULL, sorting SMALLINT DEFAULT '0' NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_7FB5DD7361220EA6 ON questionnaire_usage (creator_id)");
        $this->addSql("CREATE INDEX IDX_7FB5DD73D079F553 ON questionnaire_usage (modifier_id)");
        $this->addSql("CREATE INDEX IDX_7FB5DD73744E0351 ON questionnaire_usage (rule_id)");
        $this->addSql("CREATE INDEX IDX_7FB5DD734CE34BEC ON questionnaire_usage (part_id)");
        $this->addSql("CREATE INDEX IDX_7FB5DD73CE07E8FF ON questionnaire_usage (questionnaire_id)");
        $this->addSql("CREATE UNIQUE INDEX questionnaire_usage_unique ON questionnaire_usage (questionnaire_id, part_id, rule_id)");
        $this->addSql("CREATE TABLE filter_usage (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, rule_id INT NOT NULL, part_id INT NOT NULL, filter_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, justification VARCHAR(255) NOT NULL, sorting SMALLINT DEFAULT '0' NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_F95AAD8F61220EA6 ON filter_usage (creator_id)");
        $this->addSql("CREATE INDEX IDX_F95AAD8FD079F553 ON filter_usage (modifier_id)");
        $this->addSql("CREATE INDEX IDX_F95AAD8F744E0351 ON filter_usage (rule_id)");
        $this->addSql("CREATE INDEX IDX_F95AAD8F4CE34BEC ON filter_usage (part_id)");
        $this->addSql("CREATE INDEX IDX_F95AAD8FD395B25E ON filter_usage (filter_id)");
        $this->addSql("CREATE UNIQUE INDEX filter_usage_unique ON filter_usage (filter_id, part_id, rule_id)");
        $this->addSql("ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A661220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A6D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A6744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A64CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A6CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A6D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD7361220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD73D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD73744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD734CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD73CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_usage ADD CONSTRAINT FK_F95AAD8F61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_usage ADD CONSTRAINT FK_F95AAD8FD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_usage ADD CONSTRAINT FK_F95AAD8F744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_usage ADD CONSTRAINT FK_F95AAD8F4CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_usage ADD CONSTRAINT FK_F95AAD8FD395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("DROP TABLE filter_rule");
        $this->addSql("DROP TABLE questionnaire_formula");
        $this->addSql("DROP TABLE filter_formula");
        $this->addSql("ALTER TABLE rule DROP dtype");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
