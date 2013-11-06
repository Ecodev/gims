<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131106155354 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE TABLE note (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, question_id INT DEFAULT NULL, questionnaire_id INT DEFAULT NULL, survey_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, description VARCHAR(1023) DEFAULT NULL, attachment_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_CFBDFA1461220EA6 ON note (creator_id)");
        $this->addSql("CREATE INDEX IDX_CFBDFA14D079F553 ON note (modifier_id)");
        $this->addSql("CREATE INDEX IDX_CFBDFA141E27F6BF ON note (question_id)");
        $this->addSql("CREATE INDEX IDX_CFBDFA14CE07E8FF ON note (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_CFBDFA14B3FE509D ON note (survey_id)");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1461220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA141E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
