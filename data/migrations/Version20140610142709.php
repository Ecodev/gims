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
        $objects = array(
            'Questionnaire' => array(
                'read' => array('editor', 'reporter', 'validator'),
            ),
        );

        // Delete existing permission which were too permissive, and recreate it with correct role
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
