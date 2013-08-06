<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130802110225 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE TABLE question_part (question_id INT NOT NULL, part_id INT NOT NULL, PRIMARY KEY(question_id, part_id))");
        $this->addSql("CREATE INDEX IDX_17E19D291E27F6BF ON question_part (question_id)");
        $this->addSql("CREATE INDEX IDX_17E19D294CE34BEC ON question_part (part_id)");
        $this->addSql("ALTER TABLE question_part ADD CONSTRAINT FK_17E19D291E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question_part ADD CONSTRAINT FK_17E19D294CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

	public function down(Schema $schema)
	{
		$this->throwIrreversibleMigrationException();
	}
}
