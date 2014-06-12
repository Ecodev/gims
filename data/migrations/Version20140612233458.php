<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140612233458 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->addSql("UPDATE role SET name = 'Questionnaire reporter' WHERE name = 'reporter'");
        $this->addSql("UPDATE role SET name = 'Questionnaire validator' WHERE name = 'validator'");
        $this->addSql("UPDATE role SET name = 'Survey editor' WHERE name = 'editor'");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
