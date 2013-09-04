<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130903155106 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        $this->addSql("TRUNCATE permission CASCADE;");
        $this->addSql("TRUNCATE role_permission CASCADE;");

        $this->addSql("INSERT INTO role (name, date_created) VALUES ('editor', NOW())");

        $objects = array(
            'Answer' => array(
                'create' => array('reporter'),
                'read' => array('reporter', 'validator'),
                'update' => array('reporter'),
                'delete' => array('reporter'),
            ),
            'Choice' => array(
                // only editor or survey owner can do anything
                'create' => array('editor'),
                'read' => array('editor', 'validator'),
                'update' => array('editor'),
                'delete' => array('editor'),
            ),
            'Filter' => array(
                'create' => array('member'),
                'read' => array('anonymous', 'member'),
                'update' => array('member'),
                'delete' => array('member'),
            ),
            'FilterSet' => array(
                'create' => array('anonymous', 'member'),
                'read' => array('anonymous', 'member'),
                'update' => array('anonymous', 'member'), // but only if owner
                'delete' => array('anonymous', 'member'), // but only if owner
            ),
            'Question' => array(
                // only editor or survey owner can do anything
                'create' => array('editor'),
                'read' => array('member'), // all members can read questions. it's slighlty too permissive but we don't have to mess with double-context of survey for admin and questionnaire for reporting
                'update' => array('editor'),
                'delete' => array('editor'),
            ),
            'Questionnaire' => array(
                'create' => array('editor'),
                'read' => array('anonymous', 'member'),
                'update' => array('editor', 'reporter'),
                'delete' => array('editor', 'reporter'),
                'validate' => array('validator'),
            ),
            'Role' => array(
                'create' => array('member'),
                'read' => array('member'),
                'update' => array('member'),
                'delete' => array('member'),
            ),
            'Survey' => array(
                'create' => array('member'), // any logged user can create a new survey
                'read' => array('editor', 'validator'),
                'update' => array('editor'), //  but only selected editors or owner can update it
                'delete' => array('editor'),
            ),
            'User' => array(
                'create' => array('anonymous', 'member'),
                'read' => array('member'),
                'update' => array('member'),
                'delete' => array('member'),
            ),
            'UserQuestionnaire' => array(
                // We allow editor to elect validators for existing questionnaires
                'create' => array('editor'),
                'read' => array('editor'),
                'update' => array('editor'),
                'delete' => array('editor'),
            ),
            'UserSurvey' => array(
                // We allow editor to elect other editors (eg: to ask for help to edit things)
                'create' => array('editor'),
                'read' => array('editor'),
                'update' => array('editor'),
                'delete' => array('editor'),
            ),
            // Strictly read-only objects
            'Country' => array(
                'read' => array('anonymous', 'member'),
            ),
            'Geoname' => array(
                'read' => array('anonymous', 'member'),
            ),
            'Part' => array(
                'read' => array('anonymous', 'member'),
            ),
        );

        foreach ($objects as $object => $actions) {
            foreach ($actions as $action => $roles) {
                $name = $object . '-' . $action;
                $this->addSql('INSERT INTO permission (date_created, name) VALUES (NOW(), ?);', array($name));

                // Give access to defined roles
                foreach ($roles as $role) {
                    $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?)AND permission.name = ?;", array($role, $name));
                }
            }
        }
    }

    public function down(Schema $schema)
    {

    }

}
