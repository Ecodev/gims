<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130410154241 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("INSERT INTO role (id, name, date_created) VALUES (nextval('role_id_seq'), 'reporter', NOW())");
        $this->addSql("INSERT INTO role (id, name, date_created) VALUES (nextval('role_id_seq'), 'validator', NOW())");

        // Update permission
        $this->addSql("INSERT INTO permission (id, name, date_created) VALUES (nextval('permission_id_seq'), 'can-manage-answer', NOW())");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
