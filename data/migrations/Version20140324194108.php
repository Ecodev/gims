<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140324194108 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $add = array(
            'Rule' => array(
                'create' => array('member'),
                'read' => array('anonymous'),
                'update' => array('member'),
                'delete' => array('member')
            ),
        );

        foreach ($add as $object => $actions) {
            foreach ($actions as $action => $roles) {

                $this->addSql('INSERT INTO permission (date_created, name) VALUES (NOW(), ?);', array($object . '-' . $action));

                foreach ($roles as $role) {
                    $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?) AND permission.name = ?;", array(
                        $role,
                        $object . '-' . $action
                    ));
                }
            }
        }
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
