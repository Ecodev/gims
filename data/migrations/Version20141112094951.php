<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141112094951 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // Rename geoname
        $this->addSql("UPDATE geoname SET name = 'Channel Islands' WHERE id = 3042400");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
