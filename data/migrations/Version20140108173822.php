<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140108173822 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // prepare the reverse of the relation
        $this->addSql("DROP INDEX idx_5373c96623f5422b");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_5373C96623F5422B ON country (geoname_id)");
        $this->addSql("ALTER TABLE geoname ADD country_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE geoname RENAME COLUMN country TO country_code");
        $this->addSql("ALTER TABLE geoname ADD CONSTRAINT FK_EF41727AF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_EF41727AF92F3E70 ON geoname (country_id)");

        // update values to keep the relation
        $this->addSql("UPDATE geoname g SET country_id = (SELECT id FROM country c WHERE c.geoname_id = g.id)");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
