<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130626132451 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE TABLE filter_set_excluded_filter (filter_set_id INT NOT NULL, excluded_filter_id INT NOT NULL, PRIMARY KEY(filter_set_id, excluded_filter_id))");
        $this->addSql("CREATE INDEX IDX_E1B1EE833DD05366 ON filter_set_excluded_filter (filter_set_id)");
        $this->addSql("CREATE INDEX IDX_E1B1EE837C35DA12 ON filter_set_excluded_filter (excluded_filter_id)");
        $this->addSql("ALTER TABLE filter_set_excluded_filter ADD CONSTRAINT FK_E1B1EE833DD05366 FOREIGN KEY (filter_set_id) REFERENCES filter_set (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_set_excluded_filter ADD CONSTRAINT FK_E1B1EE837C35DA12 FOREIGN KEY (excluded_filter_id) REFERENCES filter (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
    }
}
