<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140527165704 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $objects = array(
            'Rule' => array(
                'create' => array('reporter'),
                'update' => array('reporter'),
                'delete' => array('reporter')
            ),
            'QuestionnaireUsage' => array(
                'create' => array('reporter'),
                'update' => array('reporter'),
                'delete' => array('reporter')
            ),
            'FilterQuestionnaireUsage' => array(
                'create' => array('reporter'),
                'update' => array('reporter'),
                'delete' => array('reporter')
            ),
            'FilterGeonameUsage' => array(
                'create' => array('reporter'),
                'update' => array('reporter'),
                'delete' => array('reporter')
            ),
        );

        // Delete existing permission, and recreate it with correct role
        foreach ($objects as $object => $actions) {
            foreach ($actions as $action => $roles) {
                $name = $object . '-' . $action;
                $this->addSql('DELETE FROM permission WHERE name = ?;', array($name));
                $this->addSql('INSERT INTO permission (date_created, name) VALUES (NOW(), ?);', array($name));

                // Give access to defined roles
                foreach ($roles as $role) {
                    $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?)AND permission.name = ?;", array($role, $name));
                }
            }
        }

        $this->addSql(<<<STRING
CREATE OR REPLACE FUNCTION cascade_delete_rules_with_references() RETURNS trigger AS
\$BODY\$
DECLARE
    objectType char := NULL;
    pattern varchar := NULL;
    formulaComponent CONSTANT varchar := '(Q\#|F\#|R\#|P\#|L\#|Y[+-]?|\d+|current|all|,)*';
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

        $this->addSql("CREATE TRIGGER cascade_delete_rules_with_references_to_filters AFTER DELETE ON filter FOR EACH ROW EXECUTE PROCEDURE cascade_delete_rules_with_references();");
        $this->addSql("CREATE TRIGGER cascade_delete_rules_with_references_to_questionnaires AFTER DELETE ON questionnaire FOR EACH ROW EXECUTE PROCEDURE cascade_delete_rules_with_references();");
        $this->addSql("CREATE TRIGGER cascade_delete_rules_with_references_to_parts AFTER DELETE ON part FOR EACH ROW EXECUTE PROCEDURE cascade_delete_rules_with_references();");
        $this->addSql("CREATE TRIGGER cascade_delete_rules_with_references_to_rules AFTER DELETE ON rule FOR EACH ROW EXECUTE PROCEDURE cascade_delete_rules_with_references();");

        $this->addSql("UPDATE rule SET formula = '=' WHERE formula IS NULL;");
        $this->addSql("ALTER TABLE rule ALTER formula SET DEFAULT '=';");
        $this->addSql("ALTER TABLE rule ALTER formula SET NOT NULL;");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
