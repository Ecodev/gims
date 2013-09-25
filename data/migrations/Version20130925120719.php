<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130925120719 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE TABLE filter_formula (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, part_id INT NOT NULL, filter_id INT NOT NULL, formula_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, justification VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_D0A12FE461220EA6 ON filter_formula (creator_id)");
        $this->addSql("CREATE INDEX IDX_D0A12FE4D079F553 ON filter_formula (modifier_id)");
        $this->addSql("CREATE INDEX IDX_D0A12FE44CE34BEC ON filter_formula (part_id)");
        $this->addSql("CREATE INDEX IDX_D0A12FE4D395B25E ON filter_formula (filter_id)");
        $this->addSql("CREATE INDEX IDX_D0A12FE4A50A6386 ON filter_formula (formula_id)");
        $this->addSql("CREATE UNIQUE INDEX filter_formula_unique ON filter_formula (filter_id, part_id, formula_id)");
        $this->addSql("ALTER TABLE filter_formula ADD CONSTRAINT FK_D0A12FE461220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_formula ADD CONSTRAINT FK_D0A12FE4D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_formula ADD CONSTRAINT FK_D0A12FE44CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_formula ADD CONSTRAINT FK_D0A12FE4D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_formula ADD CONSTRAINT FK_D0A12FE4A50A6386 FOREIGN KEY (formula_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
