<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Fix the wrong mapping to filters for some questions
 */
class Version20130703204100 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->addSql("UPDATE question SET filter_id = 150 WHERE id=2447"); // Bangladesh, MICS94
        $this->addSql("UPDATE question SET filter_id = 131 WHERE id=52"); // Palestine, CEN97
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
    }

}
