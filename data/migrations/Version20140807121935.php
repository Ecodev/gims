<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140807121935 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->addSql("CREATE TYPE survey_type AS ENUM ('glaas', 'jmp', 'nsa');");
        $this->addSql("ALTER TABLE survey ADD type survey_type;");
        $this->addSql("UPDATE survey SET type = 'glaas';");
        $this->addSql("UPDATE survey SET type = 'jmp' WHERE id IN (824);");
        $this->addSql("UPDATE survey SET type = 'nsa' WHERE id IN (825, 826, 827);");
        $this->addSql("UPDATE survey SET type = 'jmp' WHERE creator_id IS NULL;");
        $this->addSql("ALTER TABLE survey ALTER COLUMN type SET NOT NULL;");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
