<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130816155715 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494ED395B25E");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494ED395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_children DROP CONSTRAINT FK_9CF8B41A2A4D2D2");
        $this->addSql("ALTER TABLE filter_children ADD CONSTRAINT FK_9CF8B41A2A4D2D2 FOREIGN KEY (child_filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question_part DROP CONSTRAINT FK_17E19D291E27F6BF");
        $this->addSql("ALTER TABLE question_part ADD CONSTRAINT FK_17E19D291E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
