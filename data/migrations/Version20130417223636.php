<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130417223636 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE TABLE rule (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, dtype VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_46D8ACCC61220EA6 ON rule (creator_id)");
        $this->addSql("CREATE INDEX IDX_46D8ACCCD079F553 ON rule (modifier_id)");
        $this->addSql("CREATE TABLE category_filter_component_rule (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, category_filter_component_id INT NOT NULL, questionnaire_id INT NOT NULL, part_id INT DEFAULT NULL, rule_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_2AA9197D61220EA6 ON category_filter_component_rule (creator_id)");
        $this->addSql("CREATE INDEX IDX_2AA9197DD079F553 ON category_filter_component_rule (modifier_id)");
        $this->addSql("CREATE INDEX IDX_2AA9197D9855A551 ON category_filter_component_rule (category_filter_component_id)");
        $this->addSql("CREATE INDEX IDX_2AA9197DCE07E8FF ON category_filter_component_rule (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_2AA9197D4CE34BEC ON category_filter_component_rule (part_id)");
        $this->addSql("CREATE INDEX IDX_2AA9197D744E0351 ON category_filter_component_rule (rule_id)");
        $this->addSql("CREATE UNIQUE INDEX rule_unique ON category_filter_component_rule (category_filter_component_id, questionnaire_id, part_id)");
        $this->addSql("CREATE TABLE category_filter_component (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name TEXT NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_1A28D43E61220EA6 ON category_filter_component (creator_id)");
        $this->addSql("CREATE INDEX IDX_1A28D43ED079F553 ON category_filter_component (modifier_id)");
        $this->addSql("CREATE TABLE category_filter_component_category (category_filter_component_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(category_filter_component_id, category_id))");
        $this->addSql("CREATE INDEX IDX_123769019855A551 ON category_filter_component_category (category_filter_component_id)");
        $this->addSql("CREATE INDEX IDX_1237690112469DE2 ON category_filter_component_category (category_id)");
        $this->addSql("CREATE TABLE filter (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name TEXT NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_7FC45F1D61220EA6 ON filter (creator_id)");
        $this->addSql("CREATE INDEX IDX_7FC45F1DD079F553 ON filter (modifier_id)");
        $this->addSql("CREATE TABLE filter_category_filter_component (filter_id INT NOT NULL, category_filter_component_id INT NOT NULL, PRIMARY KEY(filter_id, category_filter_component_id))");
        $this->addSql("CREATE INDEX IDX_FEC442ABD395B25E ON filter_category_filter_component (filter_id)");
        $this->addSql("CREATE INDEX IDX_FEC442AB9855A551 ON filter_category_filter_component (category_filter_component_id)");
        $this->addSql("ALTER TABLE rule ADD CONSTRAINT FK_46D8ACCC61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE rule ADD CONSTRAINT FK_46D8ACCCD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component_rule ADD CONSTRAINT FK_2AA9197D61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component_rule ADD CONSTRAINT FK_2AA9197DD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component_rule ADD CONSTRAINT FK_2AA9197D9855A551 FOREIGN KEY (category_filter_component_id) REFERENCES category_filter_component (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component_rule ADD CONSTRAINT FK_2AA9197DCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component_rule ADD CONSTRAINT FK_2AA9197D4CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component_rule ADD CONSTRAINT FK_2AA9197D744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component ADD CONSTRAINT FK_1A28D43E61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component ADD CONSTRAINT FK_1A28D43ED079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component_category ADD CONSTRAINT FK_123769019855A551 FOREIGN KEY (category_filter_component_id) REFERENCES category_filter_component (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category_filter_component_category ADD CONSTRAINT FK_1237690112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1D61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1DD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_category_filter_component ADD CONSTRAINT FK_FEC442ABD395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_category_filter_component ADD CONSTRAINT FK_FEC442AB9855A551 FOREIGN KEY (category_filter_component_id) REFERENCES category_filter_component (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
