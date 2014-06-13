<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140610142709 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->addSql('INSERT INTO role (date_created, name) VALUES (NOW(), \'Questionnaire publisher\');');

        $objects = array(
            'Questionnaire' => array(
                'read' => array('editor', 'reporter', 'validator', 'Questionnaire publisher'),
                'validate' => array('Questionnaire publisher'), // A publisher can also validate, so he is able to un-publish something but keep it validated
                'publish' => array('Questionnaire publisher'),
                'update' => array('validator', 'Questionnaire publisher'),
            ),
            'Answer' => array(
                'read' => array('Questionnaire publisher'),
            ),
            'Choice' => array(
                'read' => array('Questionnaire publisher'),
            ),
            'Survey' => array(
                'read' => array('Questionnaire publisher'),
            ),
        );

        // Delete existing permission which were too permissive, and recreate it with correct role
        foreach ($objects as $object => $actions) {
            foreach ($actions as $action => $roles) {
                $name = $object . '-' . $action;

                if ($name == 'Questionnaire-read') {
                    $this->addSql('DELETE FROM permission WHERE name = ?;', array($name));
                }

                // Create non-existing permissions
                $this->addSql('INSERT INTO permission (date_created, name) SELECT NOW(), ? WHERE NOT EXISTS (SELECT 1 FROM permission WHERE name = ?);', array($name, $name));

                // Give access to defined roles
                foreach ($roles as $role) {
                    $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?)AND permission.name = ?;", array($role, $name));
                }
            }
        }

        // Rename existing roles
        $this->addSql("UPDATE role SET name = 'Questionnaire reporter' WHERE name = 'reporter'");
        $this->addSql("UPDATE role SET name = 'Questionnaire validator' WHERE name = 'validator'");
        $this->addSql("UPDATE role SET name = 'Survey editor' WHERE name = 'editor'");

        // Since ALTER TYPE cannot be within transaction, we have to forcefully close the current one
        $this->addSql("COMMIT TRANSACTION;");
        $this->addSql("ALTER TYPE questionnaire_status ADD VALUE 'published' AFTER 'validated';");
        $this->addSql("START TRANSACTION;");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
