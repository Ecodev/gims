<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130826173757 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE filter_set ADD original_filter_set_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE filter_set ADD CONSTRAINT FK_1C0A40EF2B18A39 FOREIGN KEY (original_filter_set_id) REFERENCES filter_set (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_1C0A40EF2B18A39 ON filter_set (original_filter_set_id)");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
