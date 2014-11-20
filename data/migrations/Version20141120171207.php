<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141120171207 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // Give read-only access on rules to anonymous
        $permissions = [
            "QuestionnaireUsage-read",
            "FilterQuestionnaireUsage-read",
            "FilterGeonameUsage-read",
            "Rule-read",
        ];

        foreach ($permissions as $permission) {
            $this->addSql("INSERT INTO role_permission (role_id, permission_id) SELECT role.id, permission.id FROM role CROSS JOIN permission WHERE (role.name = ?) AND permission.name = ?;", array('anonymous', $permission));
        }
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
