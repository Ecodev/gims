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
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
