<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141030141308 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->addSql('UPDATE "user" SET state = 1;');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN state SET DEFAULT 0;');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN state SET NOT NULL;');
        $this->addSql('ALTER TABLE "user" ADD activation_token VARCHAR(32) DEFAULT NULL;');
        $this->addSql('CREATE UNIQUE INDEX user_activation_token ON "user" (activation_token);');
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
