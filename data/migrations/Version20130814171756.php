<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130814171756 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER TABLE question ADD dtype VARCHAR(255) NOT NULL");
        $this->addSql("ALTER TABLE question ADD description TEXT DEFAULT NULL;");
        $this->addSql("ALTER TABLE question DROP official_question_id");
        $this->addSql("ALTER TABLE question DROP info");
        $this->addSql("ALTER TABLE question DROP type");
        $this->addSql("ALTER TABLE question_part DROP CONSTRAINT FK_17E19D291E27F6BF");
        $this->addSql("ALTER TABLE question_part ADD CONSTRAINT FK_17E19D291E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("DROP TYPE question_type CASCADE;");
        $this->addSql("ALTER TABLE question RENAME COLUMN parent_id TO chapter_id;");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT fk_b6f7494e727aca70;");
        $this->addSql("DROP INDEX idx_b6f7494e727aca70;");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494E579F4768 FOREIGN KEY (chapter_id) REFERENCES question (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("CREATE INDEX IDX_B6F7494E579F4768 ON question (chapter_id)");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
