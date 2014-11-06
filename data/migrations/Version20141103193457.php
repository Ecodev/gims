<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141103193457 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE filter_set ADD is_published BOOLEAN DEFAULT 'false' NOT NULL");
        $this->addSql("UPDATE filter_set SET is_published = true WHERE name IN (
    'Sanitation',
    'Sanitation: use of improved and unimproved facilities (JMP data)',
    'Sanitation: use of improved facilities (JMP data)',
    'Water',
    'Water: use of improved and unimproved sources (JMP data)',
    'Water: use of improved sources (JMP data)'
)
");

        // Delete the permission to read filterSet for anonymous and member
        $this->addSql("DELETE FROM role_permission
USING role, permission
WHERE
role_id = role.id
AND permission_id = permission.id
AND role.name IN ('anonymous', 'member')
AND permission.name = 'FilterSet-read'
");

        // Rename Note into Comment
        $this->addSql("UPDATE permission SET name = REPLACE(name, 'Note', 'Comment');");

        // Discussion and comment are create-only for everybody
        $this->addSql("DELETE FROM role_permission USING permission WHERE permission.name LIKE 'Comment-%' AND permission.id = permission_id;");
        $this->addSql("INSERT INTO permission (name) VALUES ('Discussion-create')");
        $this->addSql("INSERT INTO permission (name) VALUES ('Discussion-update')");
        $this->addSql("INSERT INTO permission (name) VALUES ('Discussion-read')");
        $this->addSql("INSERT INTO permission (name) VALUES ('Discussion-delete')");

        $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?)AND permission.name = ?;", array('anonymous', 'Comment-read'));
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?)AND permission.name = ?;", array('member', 'Comment-create'));
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?)AND permission.name = ?;", array('member', 'Comment-read'));
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?)AND permission.name = ?;", array('anonymous', 'Discussion-read'));
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?)AND permission.name = ?;", array('member', 'Discussion-create'));
        $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?)AND permission.name = ?;", array('member', 'Discussion-read'));

        // Add Dicussion and Comment (instead of note)
        $this->addSql("DROP SEQUENCE note_id_seq CASCADE");
        $this->addSql("CREATE TABLE discussion (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, survey_id INT DEFAULT NULL, questionnaire_id INT DEFAULT NULL, filter_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_C0B9F90F61220EA6 ON discussion (creator_id)");
        $this->addSql("CREATE INDEX IDX_C0B9F90FD079F553 ON discussion (modifier_id)");
        $this->addSql("CREATE INDEX IDX_C0B9F90FB3FE509D ON discussion (survey_id)");
        $this->addSql("CREATE INDEX IDX_C0B9F90FCE07E8FF ON discussion (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_C0B9F90FD395B25E ON discussion (filter_id)");
        $this->addSql("CREATE UNIQUE INDEX discussion_unique_on_survey ON discussion (survey_id) WHERE (((survey_id IS NOT NULL) AND (questionnaire_id IS NULL)) AND (survey_id IS NULL))");
        $this->addSql("CREATE UNIQUE INDEX discussion_unique_on_questionnaire ON discussion (survey_id) WHERE (((survey_id IS NULL) AND (questionnaire_id IS NOT NULL)) AND (survey_id IS NULL))");
        $this->addSql("CREATE UNIQUE INDEX discussion_unique_on_answer ON discussion (survey_id) WHERE (((survey_id IS NULL) AND (questionnaire_id IS NOT NULL)) AND (survey_id IS NOT NULL))");
        $this->addSql("CREATE TABLE comment (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, discussion_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, description VARCHAR(4096) DEFAULT NULL, attachment_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_9474526C61220EA6 ON comment (creator_id)");
        $this->addSql("CREATE INDEX IDX_9474526CD079F553 ON comment (modifier_id)");
        $this->addSql("CREATE INDEX IDX_9474526C1ADED311 ON comment (discussion_id)");
        $this->addSql("ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90F61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FD395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE comment ADD CONSTRAINT FK_9474526C61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE comment ADD CONSTRAINT FK_9474526CD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE comment ADD CONSTRAINT FK_9474526C1ADED311 FOREIGN KEY (discussion_id) REFERENCES discussion (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("DROP TABLE note");

        // Finally add a check to be sure that a discussion is correctly linked to objects
        $this->addSql("ALTER TABLE discussion ADD CONSTRAINT valid_link CHECK ((survey_id IS NOT NULL AND questionnaire_id IS NULL AND filter_id IS NULL) OR (survey_id IS NULL AND questionnaire_id IS NOT NULL AND filter_id IS NULL) OR  (survey_id IS NULL AND questionnaire_id IS NOT NULL AND filter_id IS NOT NULL));");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
