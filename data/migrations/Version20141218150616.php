<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141218150616 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        $this->addSql("DROP INDEX user_activation_token");
        $this->addSql("ALTER TABLE \"user\" ADD date_token_generated TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL");
        $this->addSql("ALTER TABLE \"user\" RENAME COLUMN activation_token TO token");
        $this->addSql("CREATE UNIQUE INDEX user_token ON \"user\" (token)");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
