<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130408192345 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE TABLE category_summand (category_id INT NOT NULL, summand_category_id INT NOT NULL, PRIMARY KEY(category_id, summand_category_id))");
        $this->addSql("CREATE INDEX IDX_8C81179912469DE2 ON category_summand (category_id)");
        $this->addSql("CREATE INDEX IDX_8C81179938B53D84 ON category_summand (summand_category_id)");
        $this->addSql("ALTER TABLE category_summand ADD CONSTRAINT FK_8C81179912469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_summand ADD CONSTRAINT FK_8C81179938B53D84 FOREIGN KEY (summand_category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("DROP INDEX category_unique");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
