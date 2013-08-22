<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130822110915 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");

        $this->addSql("CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;");
        $this->addSql("COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';");
        $this->addSql("CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;");
        $this->addSql("COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';");
        $this->addSql("CREATE TYPE questionnaire_status AS ENUM ('new', 'completed', 'validated', 'rejected');");

        $this->addSql("CREATE TABLE filter_rule (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, filter_id INT NOT NULL, questionnaire_id INT NOT NULL, part_id INT NOT NULL, rule_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, justification VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_2EE2CBB561220EA6 ON filter_rule (creator_id)");
        $this->addSql("CREATE INDEX IDX_2EE2CBB5D079F553 ON filter_rule (modifier_id)");
        $this->addSql("CREATE INDEX IDX_2EE2CBB5D395B25E ON filter_rule (filter_id)");
        $this->addSql("CREATE INDEX IDX_2EE2CBB5CE07E8FF ON filter_rule (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_2EE2CBB54CE34BEC ON filter_rule (part_id)");
        $this->addSql("CREATE INDEX IDX_2EE2CBB5744E0351 ON filter_rule (rule_id)");
        $this->addSql("CREATE UNIQUE INDEX filter_rule_unique ON filter_rule (filter_id, questionnaire_id, part_id, rule_id)");
        $this->addSql("CREATE TABLE rule (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, is_final BOOLEAN NOT NULL, dtype VARCHAR(255) NOT NULL, formula VARCHAR(4096) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_46D8ACCC61220EA6 ON rule (creator_id)");
        $this->addSql("CREATE INDEX IDX_46D8ACCCD079F553 ON rule (modifier_id)");
        $this->addSql("CREATE TABLE questionnaire_formula (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, questionnaire_id INT NOT NULL, part_id INT NOT NULL, formula_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, justification VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_38AD768661220EA6 ON questionnaire_formula (creator_id)");
        $this->addSql("CREATE INDEX IDX_38AD7686D079F553 ON questionnaire_formula (modifier_id)");
        $this->addSql("CREATE INDEX IDX_38AD7686CE07E8FF ON questionnaire_formula (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_38AD76864CE34BEC ON questionnaire_formula (part_id)");
        $this->addSql("CREATE INDEX IDX_38AD7686A50A6386 ON questionnaire_formula (formula_id)");
        $this->addSql("CREATE UNIQUE INDEX questionnaire_formula_unique ON questionnaire_formula (questionnaire_id, part_id, formula_id)");
        $this->addSql("CREATE TABLE part (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name TEXT NOT NULL, is_total BOOLEAN NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_490F70C661220EA6 ON part (creator_id)");
        $this->addSql("CREATE INDEX IDX_490F70C6D079F553 ON part (modifier_id)");
        $this->addSql("CREATE TABLE population (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, country_id INT NOT NULL, part_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, year NUMERIC(4, 0) NOT NULL, population INT NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_B449A00861220EA6 ON population (creator_id)");
        $this->addSql("CREATE INDEX IDX_B449A008D079F553 ON population (modifier_id)");
        $this->addSql("CREATE INDEX IDX_B449A008F92F3E70 ON population (country_id)");
        $this->addSql("CREATE INDEX IDX_B449A0084CE34BEC ON population (part_id)");
        $this->addSql("CREATE UNIQUE INDEX population_unique ON population (year, country_id, part_id)");
        $this->addSql("CREATE TABLE answer (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, question_id INT NOT NULL, questionnaire_id INT NOT NULL, value_user_id INT DEFAULT NULL, part_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, value_choice SMALLINT DEFAULT NULL, value_percent NUMERIC(4, 3) DEFAULT NULL, value_absolute DOUBLE PRECISION DEFAULT NULL, value_text TEXT DEFAULT NULL, quality NUMERIC(3, 2) DEFAULT NULL, relevance NUMERIC(3, 2) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_DADD4A2561220EA6 ON answer (creator_id)");
        $this->addSql("CREATE INDEX IDX_DADD4A25D079F553 ON answer (modifier_id)");
        $this->addSql("CREATE INDEX IDX_DADD4A251E27F6BF ON answer (question_id)");
        $this->addSql("CREATE INDEX IDX_DADD4A25CE07E8FF ON answer (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_DADD4A254CCCA6F5 ON answer (value_user_id)");
        $this->addSql("CREATE INDEX IDX_DADD4A254CE34BEC ON answer (part_id)");
        $this->addSql("CREATE UNIQUE INDEX answer_unique ON answer (question_id, questionnaire_id, part_id)");
        $this->addSql("CREATE TABLE country (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, geoname_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, code VARCHAR(255) NOT NULL, iso3 VARCHAR(255) DEFAULT NULL, iso_numeric INT DEFAULT NULL, fips VARCHAR(255) DEFAULT NULL, name VARCHAR(50) DEFAULT NULL, capital VARCHAR(100) DEFAULT NULL, area DOUBLE PRECISION DEFAULT NULL, continent VARCHAR(255) DEFAULT NULL, tld VARCHAR(255) DEFAULT NULL, currency_code VARCHAR(255) DEFAULT NULL, currency_name VARCHAR(20) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, postal_code_format VARCHAR(60) DEFAULT NULL, postal_code_regexp VARCHAR(150) DEFAULT NULL, languages VARCHAR(100) DEFAULT NULL, neighbors VARCHAR(75) DEFAULT NULL, equivalent_fips_code VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_5373C96661220EA6 ON country (creator_id)");
        $this->addSql("CREATE INDEX IDX_5373C966D079F553 ON country (modifier_id)");
        $this->addSql("CREATE INDEX IDX_5373C96623F5422B ON country (geoname_id)");
        $this->addSql("CREATE UNIQUE INDEX country_code_unique ON country (code)");
        $this->addSql("CREATE TABLE questionnaire (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, geoname_id INT NOT NULL, survey_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_observation_start TIMESTAMP(0) WITH TIME ZONE NOT NULL, date_observation_end TIMESTAMP(0) WITH TIME ZONE NOT NULL, status questionnaire_status NOT NULL, comments TEXT DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_7A64DAF61220EA6 ON questionnaire (creator_id)");
        $this->addSql("CREATE INDEX IDX_7A64DAFD079F553 ON questionnaire (modifier_id)");
        $this->addSql("CREATE INDEX IDX_7A64DAF23F5422B ON questionnaire (geoname_id)");
        $this->addSql("CREATE INDEX IDX_7A64DAFB3FE509D ON questionnaire (survey_id)");
        $this->addSql("CREATE TABLE user_survey (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, user_id INT NOT NULL, role_id INT NOT NULL, survey_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_C80D80C161220EA6 ON user_survey (creator_id)");
        $this->addSql("CREATE INDEX IDX_C80D80C1D079F553 ON user_survey (modifier_id)");
        $this->addSql("CREATE INDEX IDX_C80D80C1A76ED395 ON user_survey (user_id)");
        $this->addSql("CREATE INDEX IDX_C80D80C1D60322AC ON user_survey (role_id)");
        $this->addSql("CREATE INDEX IDX_C80D80C1B3FE509D ON user_survey (survey_id)");
        $this->addSql("CREATE UNIQUE INDEX user_survey_unique ON user_survey (user_id, survey_id, role_id)");
        $this->addSql("CREATE TABLE role (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_57698A6A61220EA6 ON role (creator_id)");
        $this->addSql("CREATE INDEX IDX_57698A6AD079F553 ON role (modifier_id)");
        $this->addSql("CREATE INDEX IDX_57698A6A727ACA70 ON role (parent_id)");
        $this->addSql("CREATE UNIQUE INDEX role_unique ON role (name)");
        $this->addSql("CREATE TABLE role_permission (role_id INT NOT NULL, permission_id INT NOT NULL, PRIMARY KEY(role_id, permission_id))");
        $this->addSql("CREATE INDEX IDX_6F7DF886D60322AC ON role_permission (role_id)");
        $this->addSql("CREATE INDEX IDX_6F7DF886FED90CCA ON role_permission (permission_id)");
        $this->addSql("CREATE TABLE survey (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name TEXT NOT NULL, code TEXT NOT NULL, active BOOLEAN NOT NULL, year NUMERIC(4, 0) DEFAULT NULL, comments TEXT DEFAULT NULL, date_start TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_end TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_AD5F9BFC61220EA6 ON survey (creator_id)");
        $this->addSql("CREATE INDEX IDX_AD5F9BFCD079F553 ON survey (modifier_id)");
        $this->addSql("CREATE UNIQUE INDEX survey_code_unique ON survey (code)");
        $this->addSql("CREATE TABLE permission (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_E04992AA61220EA6 ON permission (creator_id)");
        $this->addSql("CREATE INDEX IDX_E04992AAD079F553 ON permission (modifier_id)");
        $this->addSql("CREATE UNIQUE INDEX permission_unique ON permission (name)");
        $this->addSql("CREATE TABLE geoname (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name VARCHAR(200) DEFAULT NULL, asciiname VARCHAR(200) DEFAULT NULL, alternatenames VARCHAR(8000) DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, fclass VARCHAR(255) DEFAULT NULL, fcode VARCHAR(10) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, cc2 VARCHAR(60) DEFAULT NULL, admin1 VARCHAR(20) DEFAULT NULL, admin2 VARCHAR(80) DEFAULT NULL, admin3 VARCHAR(20) DEFAULT NULL, admin4 VARCHAR(20) DEFAULT NULL, population NUMERIC(10, 0) DEFAULT NULL, elevation INT DEFAULT NULL, gtopo30 INT DEFAULT NULL, timezone VARCHAR(40) DEFAULT NULL, moddate DATE DEFAULT NULL, geometry geometry DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_EF41727A61220EA6 ON geoname (creator_id)");
        $this->addSql("CREATE INDEX IDX_EF41727AD079F553 ON geoname (modifier_id)");
        $this->addSql("CREATE TABLE \"user\" (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(128) NOT NULL, state SMALLINT DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_8D93D64961220EA6 ON \"user\" (creator_id)");
        $this->addSql("CREATE INDEX IDX_8D93D649D079F553 ON \"user\" (modifier_id)");
        $this->addSql("CREATE UNIQUE INDEX user_email ON \"user\" (email)");
        $this->addSql("CREATE TABLE user_questionnaire (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, user_id INT NOT NULL, questionnaire_id INT NOT NULL, role_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_E928E0FF61220EA6 ON user_questionnaire (creator_id)");
        $this->addSql("CREATE INDEX IDX_E928E0FFD079F553 ON user_questionnaire (modifier_id)");
        $this->addSql("CREATE INDEX IDX_E928E0FFA76ED395 ON user_questionnaire (user_id)");
        $this->addSql("CREATE INDEX IDX_E928E0FFCE07E8FF ON user_questionnaire (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_E928E0FFD60322AC ON user_questionnaire (role_id)");
        $this->addSql("CREATE UNIQUE INDEX user_questionnaire_unique ON user_questionnaire (user_id, questionnaire_id, role_id)");
        $this->addSql("CREATE TABLE filter (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, questionnaire_id INT DEFAULT NULL, official_filter_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name TEXT NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_7FC45F1D61220EA6 ON filter (creator_id)");
        $this->addSql("CREATE INDEX IDX_7FC45F1DD079F553 ON filter (modifier_id)");
        $this->addSql("CREATE INDEX IDX_7FC45F1DCE07E8FF ON filter (questionnaire_id)");
        $this->addSql("CREATE INDEX IDX_7FC45F1D1182D075 ON filter (official_filter_id)");
        $this->addSql("CREATE TABLE filter_children (filter_id INT NOT NULL, child_filter_id INT NOT NULL, PRIMARY KEY(filter_id, child_filter_id))");
        $this->addSql("CREATE INDEX IDX_9CF8B41AD395B25E ON filter_children (filter_id)");
        $this->addSql("CREATE INDEX IDX_9CF8B41A2A4D2D2 ON filter_children (child_filter_id)");
        $this->addSql("CREATE TABLE filter_summand (filter_id INT NOT NULL, summand_filter_id INT NOT NULL, PRIMARY KEY(filter_id, summand_filter_id))");
        $this->addSql("CREATE INDEX IDX_FCC92082D395B25E ON filter_summand (filter_id)");
        $this->addSql("CREATE INDEX IDX_FCC920825161E09A ON filter_summand (summand_filter_id)");
        $this->addSql("CREATE TABLE filter_set (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, name TEXT NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_1C0A40E61220EA6 ON filter_set (creator_id)");
        $this->addSql("CREATE INDEX IDX_1C0A40ED079F553 ON filter_set (modifier_id)");
        $this->addSql("CREATE TABLE filter_set_filter (filter_set_id INT NOT NULL, filter_id INT NOT NULL, PRIMARY KEY(filter_set_id, filter_id))");
        $this->addSql("CREATE INDEX IDX_5EC1F853DD05366 ON filter_set_filter (filter_set_id)");
        $this->addSql("CREATE INDEX IDX_5EC1F85D395B25E ON filter_set_filter (filter_id)");
        $this->addSql("CREATE TABLE filter_set_excluded_filter (filter_set_id INT NOT NULL, excluded_filter_id INT NOT NULL, PRIMARY KEY(filter_set_id, excluded_filter_id))");
        $this->addSql("CREATE INDEX IDX_E1B1EE833DD05366 ON filter_set_excluded_filter (filter_set_id)");
        $this->addSql("CREATE INDEX IDX_E1B1EE837C35DA12 ON filter_set_excluded_filter (excluded_filter_id)");
        $this->addSql("CREATE TABLE question (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, chapter_id INT DEFAULT NULL, survey_id INT NOT NULL, filter_id INT DEFAULT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, sorting SMALLINT NOT NULL, name TEXT NOT NULL, dtype VARCHAR(255) NOT NULL, compulsory SMALLINT DEFAULT NULL, is_multiple BOOLEAN DEFAULT 'false', description TEXT DEFAULT NULL, is_final BOOLEAN DEFAULT 'false', PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_B6F7494E61220EA6 ON question (creator_id)");
        $this->addSql("CREATE INDEX IDX_B6F7494ED079F553 ON question (modifier_id)");
        $this->addSql("CREATE INDEX IDX_B6F7494E579F4768 ON question (chapter_id)");
        $this->addSql("CREATE INDEX IDX_B6F7494EB3FE509D ON question (survey_id)");
        $this->addSql("CREATE INDEX IDX_B6F7494ED395B25E ON question (filter_id)");
        $this->addSql("CREATE TABLE question_part (question_id INT NOT NULL, part_id INT NOT NULL, PRIMARY KEY(question_id, part_id))");
        $this->addSql("CREATE INDEX IDX_17E19D291E27F6BF ON question_part (question_id)");
        $this->addSql("CREATE INDEX IDX_17E19D294CE34BEC ON question_part (part_id)");
        $this->addSql("CREATE TABLE choice (id SERIAL NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, question_id INT NOT NULL, date_created TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_modified TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, sorting SMALLINT DEFAULT '0' NOT NULL, value NUMERIC(4, 3) DEFAULT NULL, label TEXT NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_C1AB5A9261220EA6 ON choice (creator_id)");
        $this->addSql("CREATE INDEX IDX_C1AB5A92D079F553 ON choice (modifier_id)");
        $this->addSql("CREATE INDEX IDX_C1AB5A921E27F6BF ON choice (question_id)");
        $this->addSql("ALTER TABLE filter_rule ADD CONSTRAINT FK_2EE2CBB561220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_rule ADD CONSTRAINT FK_2EE2CBB5D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_rule ADD CONSTRAINT FK_2EE2CBB5D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_rule ADD CONSTRAINT FK_2EE2CBB5CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_rule ADD CONSTRAINT FK_2EE2CBB54CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_rule ADD CONSTRAINT FK_2EE2CBB5744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE rule ADD CONSTRAINT FK_46D8ACCC61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE rule ADD CONSTRAINT FK_46D8ACCCD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD768661220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD7686D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD7686CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD76864CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire_formula ADD CONSTRAINT FK_38AD7686A50A6386 FOREIGN KEY (formula_id) REFERENCES rule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE part ADD CONSTRAINT FK_490F70C661220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE part ADD CONSTRAINT FK_490F70C6D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A00861220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A008D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A008F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A0084CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A2561220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A254CCCA6F5 FOREIGN KEY (value_user_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A254CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE country ADD CONSTRAINT FK_5373C96661220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE country ADD CONSTRAINT FK_5373C966D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE country ADD CONSTRAINT FK_5373C96623F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAFD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF23F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAFB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C161220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1A76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT FK_57698A6A61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT FK_57698A6AD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT FK_57698A6A727ACA70 FOREIGN KEY (parent_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFCD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE permission ADD CONSTRAINT FK_E04992AA61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE permission ADD CONSTRAINT FK_E04992AAD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE geoname ADD CONSTRAINT FK_EF41727A61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE geoname ADD CONSTRAINT FK_EF41727AD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT FK_8D93D64961220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT FK_8D93D649D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FF61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFA76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1D61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1DD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1DCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1D1182D075 FOREIGN KEY (official_filter_id) REFERENCES filter (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_children ADD CONSTRAINT FK_9CF8B41AD395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_children ADD CONSTRAINT FK_9CF8B41A2A4D2D2 FOREIGN KEY (child_filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_summand ADD CONSTRAINT FK_FCC92082D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_summand ADD CONSTRAINT FK_FCC920825161E09A FOREIGN KEY (summand_filter_id) REFERENCES filter (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_set ADD CONSTRAINT FK_1C0A40E61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_set ADD CONSTRAINT FK_1C0A40ED079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_set_filter ADD CONSTRAINT FK_5EC1F853DD05366 FOREIGN KEY (filter_set_id) REFERENCES filter_set (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_set_filter ADD CONSTRAINT FK_5EC1F85D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_set_excluded_filter ADD CONSTRAINT FK_E1B1EE833DD05366 FOREIGN KEY (filter_set_id) REFERENCES filter_set (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE filter_set_excluded_filter ADD CONSTRAINT FK_E1B1EE837C35DA12 FOREIGN KEY (excluded_filter_id) REFERENCES filter (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494E61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494ED079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494E579F4768 FOREIGN KEY (chapter_id) REFERENCES question (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494EB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494ED395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question_part ADD CONSTRAINT FK_17E19D291E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question_part ADD CONSTRAINT FK_17E19D294CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A9261220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A92D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A921E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");

        $this->addSql("ALTER TABLE answer ADD CHECK (((value_percent >= (0)::numeric) AND (value_percent <= (1)::numeric)));");
        $this->addSql("ALTER TABLE answer ADD CHECK (((quality >= (0)::numeric) AND (quality <= (1)::numeric)));");
        $this->addSql("ALTER TABLE answer ADD CHECK (((relevance >= (0)::numeric) AND (relevance <= (1)::numeric)));");
        $this->addSql("ALTER TABLE population ADD CHECK (((year >= (1900)::numeric) AND (year <= (3000)::numeric)));");
        $this->addSql("ALTER TABLE survey ADD CHECK (((year >= (1900)::numeric) AND (year <= (3000)::numeric)));");
        $this->addSql("ALTER TABLE filter ADD CONSTRAINT unofficial_filter_must_have_both_official_filter_and_questionnaire CHECK ((official_filter_id IS NULL AND questionnaire_id IS NULL) OR (official_filter_id IS NOT NULL AND questionnaire_id IS NOT NULL));");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT answerable_question_must_have_filter CHECK (NOT(dtype != 'chapter' AND filter_id IS NULL));");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

}
