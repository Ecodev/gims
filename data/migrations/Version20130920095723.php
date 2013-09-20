<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130920095723 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE answer RENAME COLUMN value_choice TO value_choice_id");
        $this->addSql("ALTER TABLE answer ALTER COLUMN value_choice_id SET DEFAULT null");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25DC146367 FOREIGN KEY (value_choice_id) REFERENCES choice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_DADD4A25DC146367 ON answer (value_choice_id)");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
