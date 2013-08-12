<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130812120559 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("ALTER SEQUENCE question_choice_id_seq RENAME TO choice_id_seq");
        $this->addSql("ALTER TABLE question_choice DROP CONSTRAINT fk_c6f6759a1e27f6bf;");
        $this->addSql("ALTER TABLE question_choice DROP CONSTRAINT fk_c6f6759a61220ea6;");
        $this->addSql("ALTER TABLE question_choice DROP CONSTRAINT fk_c6f6759ad079f553;");
        $this->addSql("DROP INDEX idx_c6f6759a1e27f6bf;");
        $this->addSql("DROP INDEX idx_c6f6759a61220ea6;");
        $this->addSql("DROP INDEX idx_c6f6759ad079f553;");
        $this->addSql("ALTER TABLE question_choice RENAME TO choice");

        $this->addSql("CREATE INDEX IDX_C1AB5A9261220EA6 ON choice (creator_id)");
        $this->addSql("CREATE INDEX IDX_C1AB5A92D079F553 ON choice (modifier_id)");
        $this->addSql("CREATE INDEX IDX_C1AB5A921E27F6BF ON choice (question_id)");
        $this->addSql("ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A9261220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A92D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A921E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {

        $this->throwIrreversibleMigrationException();
    }
}
