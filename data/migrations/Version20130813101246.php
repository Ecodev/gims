<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130813101246 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("TRUNCATE filter CASCADE"); // Delete all filter, because data are incomplete and we must re-import
        $this->addSql("ALTER TABLE filter ADD questionnaire_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE filter DROP is_official");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1DCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE INDEX IDX_7FC45F1DCE07E8FF ON filter (questionnaire_id)");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT unofficial_filter_must_have_both_official_filter_and_questionnaire CHECK ((official_filter_id IS NULL AND questionnaire_id IS NULL) OR (official_filter_id IS NOT NULL AND questionnaire_id IS NOT NULL));");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
