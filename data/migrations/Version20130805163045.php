<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130805163045 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");


        $this->addSql("DELETE FROM question_choice WHERE question_id IS NULL");
        $this->addSql("ALTER TABLE question_choice ALTER question_id SET NOT NULL");
        $this->addSql("ALTER TABLE question_choice DROP CONSTRAINT FK_C6F6759A1E27F6BF");
        $this->addSql("ALTER TABLE question_choice ADD CONSTRAINT FK_C6F6759A1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {

        $this->throwIrreversibleMigrationException();
    }

}
