<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140509160547 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("DROP INDEX population_unique");
        $this->addSql("ALTER TABLE population ADD questionnaire_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A008CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_B449A008CE07E8FF ON population (questionnaire_id)");
        $this->addSql("CREATE UNIQUE INDEX population_unique_official ON population (year, country_id, part_id) WHERE questionnaire_id IS NULL");
        $this->addSql("CREATE UNIQUE INDEX population_unique_non_official ON population (year, country_id, part_id, questionnaire_id) WHERE questionnaire_id IS NOT NULL");

        $objects = array(
            'Population' => array(
                'create' => array('reporter'),
                'read' => array('anonymous', 'member'),
                'update' => array('reporter'),
                'delete' => array('reporter'),
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

        $this->addSql("ALTER TABLE question ADD alternate_names JSON DEFAULT '[]' NOT NULL;");
        $this->addSql("ALTER TABLE question ALTER description SET DEFAULT '';");
        $this->addSql("CREATE UNIQUE INDEX answerable_question_must_have_unique_filter_within_same_survey ON question (survey_id, filter_id);");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
