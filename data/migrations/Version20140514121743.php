<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140514121743 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("DROP TABLE filter_set_excluded_filter");
        $this->addSql("ALTER TABLE filter_set DROP CONSTRAINT fk_1c0a40ef2b18a39");
        $this->addSql("DROP INDEX idx_1c0a40ef2b18a39");
        $this->addSql("ALTER TABLE filter_set DROP original_filter_set_id");
        $this->addSql("UPDATE questionnaire SET comments = '' WHERE comments IS NULL");
        $this->addSql("ALTER TABLE questionnaire ALTER comments SET NOT NULL;");
        $this->addSql("ALTER TABLE questionnaire ALTER comments SET DEFAULT '';");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
