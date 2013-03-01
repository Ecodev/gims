<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130301130242 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;");
        $this->addSql("COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';");
        $this->addSql("CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;");
        $this->addSql("COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';");
        $this->addSql("CREATE SEQUENCE answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE country_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE questionnaire_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE user_survey_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE survey_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE geoname_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE \"user_id_seq\" INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE user_questionnaire_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE question_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE TABLE answer (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, question_id INT DEFAULT NULL, questionnaire_id INT DEFAULT NULL, value_user_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, value_choice SMALLINT DEFAULT NULL, value_percent NUMERIC(10, 0) DEFAULT NULL, value_absolute DOUBLE PRECISION DEFAULT NULL, value_text TEXT DEFAULT NULL, quality NUMERIC(10, 0) DEFAULT NULL, relevance NUMERIC(10, 0) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_DADD4A2561220EA6 ON answer (creator_id)");
        $this->addSql("CREATE INDEX IDX_DADD4A25D079F553 ON answer (modifier_id)");
        $this->addSql("CREATE INDEX IDX_DADD4A251E27F6BF ON answer (question_id)");
        $this->addSql("CREATE INDEX IDX_DADD4A25CE07E8FF ON answer (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_DADD4A254CCCA6F5 ON answer (value_user_id)");
        $this->addSql("CREATE TABLE country (id VARCHAR(255) NOT NULL, geoname_id INT DEFAULT NULL, iso3 VARCHAR(255) DEFAULT NULL, iso_numeric INT DEFAULT NULL, fips VARCHAR(255) DEFAULT NULL, name VARCHAR(50) DEFAULT NULL, capital VARCHAR(100) DEFAULT NULL, area DOUBLE PRECISION DEFAULT NULL, population INT DEFAULT NULL, continent VARCHAR(255) DEFAULT NULL, tld VARCHAR(255) DEFAULT NULL, currency_code VARCHAR(255) DEFAULT NULL, currency_name VARCHAR(20) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, postal_code_format VARCHAR(60) DEFAULT NULL, postal_code_regexp VARCHAR(150) DEFAULT NULL, languages VARCHAR(100) DEFAULT NULL, neighbors VARCHAR(75) DEFAULT NULL, equivalent_fips_code VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_5373C96623F5422B ON country (geoname_id)");
        $this->addSql("CREATE TABLE questionnaire (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, geoname_id INT DEFAULT NULL, survey_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_observation_start TIMESTAMP(0) WITH TIME ZONE NOT NULL, date_observation_end TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_7A64DAF61220EA6 ON questionnaire (creator_id)");
        $this->addSql("CREATE INDEX IDX_7A64DAFD079F553 ON questionnaire (modifier_id)");
        $this->addSql("CREATE INDEX IDX_7A64DAF23F5422B ON questionnaire (geoname_id)");
        $this->addSql("CREATE INDEX IDX_7A64DAFB3FE509D ON questionnaire (survey_id)");
        $this->addSql("CREATE TABLE user_survey (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, user_id INT DEFAULT NULL, role_id INT DEFAULT NULL, survey_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_C80D80C161220EA6 ON user_survey (creator_id)");
        $this->addSql("CREATE INDEX IDX_C80D80C1D079F553 ON user_survey (modifier_id)");
        $this->addSql("CREATE INDEX IDX_C80D80C1A76ED395 ON user_survey (user_id)");
        $this->addSql("CREATE INDEX IDX_C80D80C1D60322AC ON user_survey (role_id)");
        $this->addSql("CREATE INDEX IDX_C80D80C1B3FE509D ON user_survey (survey_id)");
        $this->addSql("CREATE TABLE role (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, can_validate_questionnaire BOOLEAN NOT NULL, can_link_official_question BOOLEAN NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_57698A6A61220EA6 ON role (creator_id)");
        $this->addSql("CREATE INDEX IDX_57698A6AD079F553 ON role (modifier_id)");
        $this->addSql("CREATE TABLE survey (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name TEXT NOT NULL, active BOOLEAN NOT NULL, year NUMERIC(10, 0) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_AD5F9BFC61220EA6 ON survey (creator_id)");
        $this->addSql("CREATE INDEX IDX_AD5F9BFCD079F553 ON survey (modifier_id)");
        $this->addSql("CREATE TABLE setting (id VARCHAR(64) NOT NULL, value TEXT DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE TABLE category (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, official_category_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name TEXT NOT NULL, official BOOLEAN NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_64C19C161220EA6 ON category (creator_id)");
        $this->addSql("CREATE INDEX IDX_64C19C1D079F553 ON category (modifier_id)");
        $this->addSql("CREATE INDEX IDX_64C19C1C78AA2E8 ON category (official_category_id)");
        $this->addSql("CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)");
        $this->addSql("CREATE TABLE geoname (id INT NOT NULL, name VARCHAR(200) DEFAULT NULL, asciiname VARCHAR(200) DEFAULT NULL, alternatenames VARCHAR(8000) DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, fclass VARCHAR(255) DEFAULT NULL, fcode VARCHAR(10) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, cc2 VARCHAR(60) DEFAULT NULL, admin1 VARCHAR(20) DEFAULT NULL, admin2 VARCHAR(80) DEFAULT NULL, admin3 VARCHAR(20) DEFAULT NULL, admin4 VARCHAR(20) DEFAULT NULL, population NUMERIC(10, 0) DEFAULT NULL, elevation INT DEFAULT NULL, gtopo30 INT DEFAULT NULL, timezone VARCHAR(40) DEFAULT NULL, moddate DATE DEFAULT NULL, geometry geometry DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE TABLE \"user\" (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(128) NOT NULL, state SMALLINT DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_8D93D64961220EA6 ON \"user\" (creator_id)");
        $this->addSql("CREATE INDEX IDX_8D93D649D079F553 ON \"user\" (modifier_id)");
        $this->addSql("CREATE TABLE user_questionnaire (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, user_id INT DEFAULT NULL, questionnaire_id INT DEFAULT NULL, role_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_E928E0FF61220EA6 ON user_questionnaire (creator_id)");
        $this->addSql("CREATE INDEX IDX_E928E0FFD079F553 ON user_questionnaire (modifier_id)");
        $this->addSql("CREATE INDEX IDX_E928E0FFA76ED395 ON user_questionnaire (user_id)");
        $this->addSql("CREATE INDEX IDX_E928E0FFCE07E8FF ON user_questionnaire (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_E928E0FFD60322AC ON user_questionnaire (role_id)");
        $this->addSql("CREATE TABLE question (id INT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, category_id INT DEFAULT NULL, official_question_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, questionnaire_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, \"order\" SMALLINT NOT NULL, type VARCHAR(255) NOT NULL, name TEXT NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_B6F7494E61220EA6 ON question (creator_id)");
        $this->addSql("CREATE INDEX IDX_B6F7494ED079F553 ON question (modifier_id)");
        $this->addSql("CREATE INDEX IDX_B6F7494E12469DE2 ON question (category_id)");
        $this->addSql("CREATE INDEX IDX_B6F7494ECBEBC9B5 ON question (official_question_id)");
        $this->addSql("CREATE INDEX IDX_B6F7494E727ACA70 ON question (parent_id)");
        $this->addSql("CREATE INDEX IDX_B6F7494ECE07E8FF ON question (questionnaire_id)");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A2561220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A254CCCA6F5 FOREIGN KEY (value_user_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE country ADD CONSTRAINT FK_5373C96623F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAFD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF23F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAFB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C161220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1A76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT FK_57698A6A61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT FK_57698A6AD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFCD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT FK_64C19C161220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT FK_64C19C1D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT FK_64C19C1C78AA2E8 FOREIGN KEY (official_category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT FK_8D93D64961220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT FK_8D93D649D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FF61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFA76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFD60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494E61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494ED079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494ECBEBC9B5 FOREIGN KEY (official_question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494E727ACA70 FOREIGN KEY (parent_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494ECE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT FK_DADD4A25CE07E8FF");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT FK_E928E0FFCE07E8FF");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494ECE07E8FF");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT FK_C80D80C1D60322AC");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT FK_E928E0FFD60322AC");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT FK_7A64DAFB3FE509D");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT FK_C80D80C1B3FE509D");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT FK_64C19C1C78AA2E8");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT FK_64C19C1727ACA70");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494E12469DE2");
        $this->addSql("ALTER TABLE country DROP CONSTRAINT FK_5373C96623F5422B");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT FK_7A64DAF23F5422B");
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT FK_DADD4A251E27F6BF");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494ECBEBC9B5");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494E727ACA70");
        $this->addSql("DROP SEQUENCE answer_id_seq");
        $this->addSql("DROP SEQUENCE country_id_seq");
        $this->addSql("DROP SEQUENCE questionnaire_id_seq");
        $this->addSql("DROP SEQUENCE user_survey_id_seq");
        $this->addSql("DROP SEQUENCE role_id_seq");
        $this->addSql("DROP SEQUENCE survey_id_seq");
        $this->addSql("DROP SEQUENCE category_id_seq");
        $this->addSql("DROP SEQUENCE geoname_id_seq");
        $this->addSql("DROP SEQUENCE \"user_id_seq\"");
        $this->addSql("DROP SEQUENCE user_questionnaire_id_seq");
        $this->addSql("DROP SEQUENCE question_id_seq");
        $this->addSql("DROP TABLE answer");
        $this->addSql("DROP TABLE country");
        $this->addSql("DROP TABLE questionnaire");
        $this->addSql("DROP TABLE user_survey");
        $this->addSql("DROP TABLE role");
        $this->addSql("DROP TABLE survey");
        $this->addSql("DROP TABLE setting");
        $this->addSql("DROP TABLE category");
        $this->addSql("DROP TABLE geoname");
        $this->addSql("DROP TABLE \"user\"");
        $this->addSql("DROP TABLE user_questionnaire");
        $this->addSql("DROP TABLE question");
        $this->addSql("DROP EXTENSION plpgsql CASCADE");
        $this->addSql("DROP EXTENSION postgis CASCADE");
    }
}
