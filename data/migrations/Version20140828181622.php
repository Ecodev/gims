<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140828181622 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("DROP INDEX filter_questionnaire_usage_unique");
        $this->addSql("ALTER TABLE filter_questionnaire_usage RENAME COLUMN is_second_level TO is_second_step");
        $this->addSql("CREATE UNIQUE INDEX filter_questionnaire_usage_unique ON filter_questionnaire_usage (filter_id, questionnaire_id, part_id, rule_id, is_second_step)");
        $this->addSql("UPDATE rule SET formula = REPLACE(formula, 'L#2', 'S#2')");

        $this->addSql(<<<STRING
CREATE OR REPLACE FUNCTION cascade_delete_rules_with_references() RETURNS trigger AS
\$BODY\$
DECLARE
    objectType char := NULL;
    pattern varchar := NULL;
    formulaComponent CONSTANT varchar := '(Q\#|F\#|R\#|P\#|S\#|Y[+-]?|\d+|current|all|,)*';
BEGIN

    -- Find what type of reference we are looking for
    CASE TG_TABLE_NAME
        WHEN 'filter' THEN
            objectType = 'F';
        WHEN 'questionnaire' THEN
            objectType = 'Q';
        WHEN 'part' THEN
            objectType = 'P';
        WHEN 'rule' THEN
            objectType = 'R';
        ELSE
            RAISE 'table "%" not supported for custom rule integrity checks', TG_TABLE_NAME;
    END CASE;

    -- Build regexp pattern to find any reference of the deleted object
    pattern := CONCAT('{', formulaComponent, objectType , '#', OLD.id, '(?!\d)', formulaComponent,'}');
    -- RAISE NOTICE '%, %, %', objectType, OLD.id, pattern;

    -- Delete all rules containing a reference to the deleted object
    DELETE FROM rule WHERE formula ~ pattern;

    RETURN NULL;
END;
\$BODY\$
  LANGUAGE plpgsql VOLATILE
  COST 100;
STRING
        );
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
