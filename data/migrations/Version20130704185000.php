<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Extend the Rule model to indicate if it is final
 */
class Version20130704185000 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE rule ADD COLUMN is_final boolean NOT NULL DEFAULT false;");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
    }

}
