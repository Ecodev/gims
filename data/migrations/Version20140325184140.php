<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140325184140 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE filter DROP official_filter_id;');
        $this->addSql('ALTER TABLE filter DROP questionnaire_id;');
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
