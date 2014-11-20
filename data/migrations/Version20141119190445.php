<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141119190445 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        // Delete countries that are not used by JMP (or GLAAS), and not used in regions
        $this->addSql("
DELETE FROM geoname WHERE
id > 30 -- not a region
AND id NOT IN (SELECT geoname_id FROM questionnaire WHERE geoname_id IS NOT NULL) -- no questionnaire exists
AND id NOT IN (SELECT geoname_id FROM filter_geoname_usage WHERE geoname_id IS NOT NULL) -- no rule are used
AND id NOT IN (SELECT geoname_id FROM \"user\" WHERE geoname_id IS NOT NULL) -- no user are from that country
AND id NOT IN (SELECT child_geoname_id FROM geoname_children WHERE geoname_id != 26) -- not part of a region other than world");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
