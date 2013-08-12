<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130812173449 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("DROP SEQUENCE questionnaire_rule_id_seq CASCADE");
        $this->addSql("CREATE TABLE questionnaire_formula (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, questionnaire_id INT NOT NULL, part_id INT NOT NULL, formula_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, justification VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_38AD768661220EA6 ON questionnaire_formula (creator_id)");
        $this->addSql("CREATE INDEX IDX_38AD7686D079F553 ON questionnaire_formula (modifier_id)");
        $this->addSql("CREATE INDEX IDX_38AD7686CE07E8FF ON questionnaire_formula (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_38AD76864CE34BEC ON questionnaire_formula (part_id)");
        $this->addSql("CREATE INDEX IDX_38AD7686A50A6386 ON questionnaire_formula (formula_id)");
        $this->addSql("CREATE UNIQUE INDEX questionnaire_formula_unique ON questionnaire_formula (questionnaire_id, part_id, formula_id)");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD768661220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD7686D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD7686CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD76864CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD7686A50A6386 FOREIGN KEY (formula_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("INSERT INTO questionnaire_formula (creator_id, modifier_id, questionnaire_id, part_id, formula_id, date_created, date_modified, justification) (SELECT creator_id, modifier_id, questionnaire_id, part_id, rule_id, date_created, date_modified, justification FROM questionnaire_rule)");
        $this->addSql("DROP TABLE questionnaire_rule");
        $this->addSql("ALTER TABLE rule DROP value");
    }

    public function down(Schema $schema)
    {

        $this->throwIrreversibleMigrationException();
    }
}
