<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140910111908 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE filter ADD thematic_filter_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE filter ADD is_thematic BOOLEAN DEFAULT 'false' NOT NULL");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1DA7D75635 FOREIGN KEY (thematic_filter_id) REFERENCES filter (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_7FC45F1DA7D75635 ON filter (thematic_filter_id)");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
