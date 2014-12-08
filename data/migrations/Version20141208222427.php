<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141208222427 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE TABLE activity (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, action TEXT NOT NULL, data JSON DEFAULT '{}' NOT NULL, record_id SMALLINT NOT NULL, record_type TEXT NOT NULL, changes JSON DEFAULT '{}' NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_AC74095A61220EA6 ON activity (creator_id)");
        $this->addSql("CREATE INDEX IDX_AC74095AD079F553 ON activity (modifier_id)");
        $this->addSql("ALTER TABLE activity ADD CONSTRAINT FK_AC74095A61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE activity ADD CONSTRAINT FK_AC74095AD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
