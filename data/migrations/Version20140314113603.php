<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140314113603 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE TABLE user_filter_set (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, user_id INT NOT NULL, filter_set_id INT NOT NULL, role_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_A6A762E261220EA6 ON user_filter_set (creator_id)");
        $this->addSql("CREATE INDEX IDX_A6A762E2D079F553 ON user_filter_set (modifier_id)");
        $this->addSql("CREATE INDEX IDX_A6A762E2A76ED395 ON user_filter_set (user_id)");
        $this->addSql("CREATE INDEX IDX_A6A762E23DD05366 ON user_filter_set (filter_set_id)");
        $this->addSql("CREATE INDEX IDX_A6A762E2D60322AC ON user_filter_set (role_id)");
        $this->addSql("CREATE UNIQUE INDEX user_filterset_unique ON user_filter_set (user_id, filter_set_id, role_id)");
        $this->addSql("ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E261220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E2D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E2A76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E23DD05366 FOREIGN KEY (filter_set_id) REFERENCES filter_set (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E2D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");

        // MANAGE ROLES

        // Create and rename
        //        $this->addSql('UPDATE role (date_modified, name) VALUES (NOW(), ?) WHERE name=?;', array('editor', 'Questionnaire/Survey editor'));
        //        $this->addSql('UPDATE role (date_modified, name) VALUES (NOW(), ?) WHERE name=?;', array('validator', 'Questionnaire validator'));
        //        $this->addSql('UPDATE role (date_modified, name) VALUES (NOW(), ?) WHERE name=?;', array('reporter', 'Questionnaire reporter'));

        // RELATIONS TO REMOVE
        $remove = array(
            'Filter' => array(
                'update' => array('member'),
                'delete' => array('member'),
            ),
            'FilterSet' => array(
                'create' => array('anonymous'),
                'update' => array('anonymous', 'member'),
                'delete' => array('anonymous', 'member'),
            )
        );
        foreach ($remove as $object => $actions) {
            foreach ($actions as $action => $roles) {
                foreach ($roles as $role) {
                    $this->addSql("DELETE FROM role_permission USING permission, role WHERE permission.id = permission_id AND role.id = role_id AND role.name=? AND permission.name=?;", array(
                        $role,
                        $object . '-' . $action
                    ));
                }
            }
        }

        // RELATIONS TO ADD TO ADD
        $this->addSql('INSERT INTO role (date_created, name) VALUES (NOW(), ?);', array('Filter editor'));
        $this->addSql('INSERT INTO permission (date_created, name) VALUES (NOW(), ?);', array('UserFilterSet-create'));
        $this->addSql('INSERT INTO permission (date_created, name) VALUES (NOW(), ?);', array('UserFilterSet-read'));
        $this->addSql('INSERT INTO permission (date_created, name) VALUES (NOW(), ?);', array('UserFilterSet-update'));
        $this->addSql('INSERT INTO permission (date_created, name) VALUES (NOW(), ?);', array('UserFilterSet-delete'));

        $add = array(
            'FilterSet' => array(
                'create' => array('Filter editor'),
                'read' => array('Filter editor'),
                'update' => array('Filter editor'),
                'delete' => array('Filter editor')
            ),
            'Filter' => array(
                'create' => array('Filter editor'),
                'read' => array('Filter editor'),
                'update' => array('Filter editor'),
                'delete' => array('Filter editor')
            ),
            'UserFilterSet' => array(
                'create' => array('Filter editor'),
                'read' => array('Filter editor'),
                'update' => array('Filter editor'),
                'delete' => array('Filter editor')
            )
        );

        foreach ($add as $object => $actions) {
            foreach ($actions as $action => $roles) {
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
