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
--role.id = 5
role_id = role.id
AND permission_id = permission.id
AND role.name IN ('anonymous', 'member')
AND permission.name = 'FilterSet-read'
");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
