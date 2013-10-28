<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131026174731 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("DROP SEQUENCE filter_usage_id_seq CASCADE");
        $this->addSql("CREATE TABLE filter_geoname_usage (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, rule_id INT NOT NULL, part_id INT NOT NULL, filter_id INT NOT NULL, geoname_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, justification VARCHAR(255) NOT NULL, sorting SMALLINT DEFAULT '0' NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_9CF42C2661220EA6 ON filter_geoname_usage (creator_id)");
        $this->addSql("CREATE INDEX IDX_9CF42C26D079F553 ON filter_geoname_usage (modifier_id)");
        $this->addSql("CREATE INDEX IDX_9CF42C26744E0351 ON filter_geoname_usage (rule_id)");
        $this->addSql("CREATE INDEX IDX_9CF42C264CE34BEC ON filter_geoname_usage (part_id)");
        $this->addSql("CREATE INDEX IDX_9CF42C26D395B25E ON filter_geoname_usage (filter_id)");
        $this->addSql("CREATE INDEX IDX_9CF42C2623F5422B ON filter_geoname_usage (geoname_id)");
        $this->addSql("CREATE UNIQUE INDEX filter_geoname_usage_unique ON filter_geoname_usage (filter_id, geoname_id, part_id, rule_id)");
        $this->addSql("ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C2661220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C26D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C26744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C264CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C26D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C2623F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("DROP TABLE filter_usage");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
