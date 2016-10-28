<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161029112921 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_57698A6A61220EA6 (creator_id), INDEX IDX_57698A6AD079F553 (modifier_id), INDEX IDX_57698A6A727ACA70 (parent_id), UNIQUE INDEX role_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_permission (role_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_6F7DF886D60322AC (role_id), INDEX IDX_6F7DF886FED90CCA (permission_id), PRIMARY KEY(role_id, permission_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, geoname_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(128) NOT NULL, phone VARCHAR(25) DEFAULT NULL, skype VARCHAR(255) DEFAULT NULL, job VARCHAR(255) DEFAULT NULL, ministry VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip VARCHAR(10) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state SMALLINT DEFAULT 0 NOT NULL, last_login DATETIME DEFAULT NULL, first_login DATETIME DEFAULT NULL, token VARCHAR(32) DEFAULT NULL, date_token_generated DATETIME DEFAULT NULL, INDEX IDX_8D93D64961220EA6 (creator_id), INDEX IDX_8D93D649D079F553 (modifier_id), INDEX IDX_8D93D64923F5422B (geoname_id), UNIQUE INDEX user_email (email), UNIQUE INDEX user_token (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_E04992AA61220EA6 (creator_id), INDEX IDX_E04992AAD079F553 (modifier_id), UNIQUE INDEX permission_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rule (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, name VARCHAR(255) NOT NULL, formula VARCHAR(4096) DEFAULT \'=\' NOT NULL, INDEX IDX_46D8ACCC61220EA6 (creator_id), INDEX IDX_46D8ACCCD079F553 (modifier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter_questionnaire_usage (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, part_id INT NOT NULL, rule_id INT NOT NULL, questionnaire_id INT NOT NULL, filter_id INT NOT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, justification VARCHAR(255) NOT NULL, sorting SMALLINT DEFAULT 0 NOT NULL, is_second_step TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_F5F3A4A661220EA6 (creator_id), INDEX IDX_F5F3A4A6D079F553 (modifier_id), INDEX IDX_F5F3A4A64CE34BEC (part_id), INDEX IDX_F5F3A4A6744E0351 (rule_id), INDEX IDX_F5F3A4A6CE07E8FF (questionnaire_id), INDEX IDX_F5F3A4A6D395B25E (filter_id), UNIQUE INDEX filter_questionnaire_usage_unique (filter_id, questionnaire_id, part_id, rule_id, is_second_step), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter_geoname_usage (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, part_id INT NOT NULL, rule_id INT NOT NULL, filter_id INT NOT NULL, geoname_id INT NOT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, justification VARCHAR(255) NOT NULL, sorting SMALLINT DEFAULT 0 NOT NULL, INDEX IDX_9CF42C2661220EA6 (creator_id), INDEX IDX_9CF42C26D079F553 (modifier_id), INDEX IDX_9CF42C264CE34BEC (part_id), INDEX IDX_9CF42C26744E0351 (rule_id), INDEX IDX_9CF42C26D395B25E (filter_id), INDEX IDX_9CF42C2623F5422B (geoname_id), UNIQUE INDEX filter_geoname_usage_unique (filter_id, geoname_id, part_id, rule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE questionnaire_usage (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, part_id INT NOT NULL, rule_id INT NOT NULL, questionnaire_id INT NOT NULL, thematic_filter_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, justification VARCHAR(255) NOT NULL, sorting SMALLINT DEFAULT 0 NOT NULL, INDEX IDX_7FB5DD7361220EA6 (creator_id), INDEX IDX_7FB5DD73D079F553 (modifier_id), INDEX IDX_7FB5DD734CE34BEC (part_id), INDEX IDX_7FB5DD73744E0351 (rule_id), INDEX IDX_7FB5DD73CE07E8FF (questionnaire_id), INDEX IDX_7FB5DD73A7D75635 (thematic_filter_id), UNIQUE INDEX questionnaire_usage_unique (questionnaire_id, part_id, rule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, chapter_id INT DEFAULT NULL, survey_id INT NOT NULL, filter_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, sorting SMALLINT NOT NULL, name LONGTEXT NOT NULL, alternate_names LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', dtype VARCHAR(255) NOT NULL, is_compulsory TINYINT(1) DEFAULT \'1\', is_population TINYINT(1) DEFAULT \'0\', is_multiple TINYINT(1) DEFAULT \'0\', description LONGTEXT DEFAULT NULL, is_final TINYINT(1) DEFAULT \'0\', is_absolute TINYINT(1) DEFAULT \'0\', INDEX IDX_B6F7494E61220EA6 (creator_id), INDEX IDX_B6F7494ED079F553 (modifier_id), INDEX IDX_B6F7494E579F4768 (chapter_id), INDEX IDX_B6F7494EB3FE509D (survey_id), INDEX IDX_B6F7494ED395B25E (filter_id), UNIQUE INDEX answerable_question_must_have_unique_filter_within_same_survey (survey_id, filter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question_part (question_id INT NOT NULL, part_id INT NOT NULL, INDEX IDX_17E19D291E27F6BF (question_id), INDEX IDX_17E19D294CE34BEC (part_id), PRIMARY KEY(question_id, part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE choice (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, question_id INT NOT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, sorting SMALLINT DEFAULT 0 NOT NULL, value NUMERIC(4, 3) DEFAULT NULL, name LONGTEXT NOT NULL, INDEX IDX_C1AB5A9261220EA6 (creator_id), INDEX IDX_C1AB5A92D079F553 (modifier_id), INDEX IDX_C1AB5A921E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter_set (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, name LONGTEXT NOT NULL, is_published TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_1C0A40E61220EA6 (creator_id), INDEX IDX_1C0A40ED079F553 (modifier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter_set_filter (filter_set_id INT NOT NULL, filter_id INT NOT NULL, INDEX IDX_5EC1F853DD05366 (filter_set_id), INDEX IDX_5EC1F85D395B25E (filter_id), PRIMARY KEY(filter_set_id, filter_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_filter_set (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, role_id INT NOT NULL, user_id INT NOT NULL, filter_set_id INT NOT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, INDEX IDX_A6A762E261220EA6 (creator_id), INDEX IDX_A6A762E2D079F553 (modifier_id), INDEX IDX_A6A762E2D60322AC (role_id), INDEX IDX_A6A762E2A76ED395 (user_id), INDEX IDX_A6A762E23DD05366 (filter_set_id), UNIQUE INDEX user_filterset_unique (user_id, filter_set_id, role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discussion (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, survey_id INT DEFAULT NULL, questionnaire_id INT DEFAULT NULL, filter_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, INDEX IDX_C0B9F90F61220EA6 (creator_id), INDEX IDX_C0B9F90FD079F553 (modifier_id), INDEX IDX_C0B9F90FB3FE509D (survey_id), INDEX IDX_C0B9F90FCE07E8FF (questionnaire_id), INDEX IDX_C0B9F90FD395B25E (filter_id), UNIQUE INDEX discussion_unique (survey_id, questionnaire_id, filter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geoname (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, name VARCHAR(200) DEFAULT NULL, geometry GEOMETRY DEFAULT NULL COMMENT \'(DC2Type:geometry)\', iso3 VARCHAR(255) DEFAULT NULL, INDEX IDX_EF41727A61220EA6 (creator_id), INDEX IDX_EF41727AD079F553 (modifier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geoname_children (geoname_id INT NOT NULL, child_geoname_id INT NOT NULL, INDEX IDX_1C1FCE623F5422B (geoname_id), INDEX IDX_1C1FCE6C72ABC40 (child_geoname_id), PRIMARY KEY(geoname_id, child_geoname_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, action LONGTEXT NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', record_id INT NOT NULL, record_type LONGTEXT NOT NULL, changes LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_AC74095A61220EA6 (creator_id), INDEX IDX_AC74095AD079F553 (modifier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_survey (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, role_id INT NOT NULL, user_id INT NOT NULL, survey_id INT NOT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, INDEX IDX_C80D80C161220EA6 (creator_id), INDEX IDX_C80D80C1D079F553 (modifier_id), INDEX IDX_C80D80C1D60322AC (role_id), INDEX IDX_C80D80C1A76ED395 (user_id), INDEX IDX_C80D80C1B3FE509D (survey_id), UNIQUE INDEX user_survey_unique (user_id, survey_id, role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_questionnaire (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, role_id INT NOT NULL, user_id INT NOT NULL, questionnaire_id INT NOT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, INDEX IDX_E928E0FF61220EA6 (creator_id), INDEX IDX_E928E0FFD079F553 (modifier_id), INDEX IDX_E928E0FFD60322AC (role_id), INDEX IDX_E928E0FFA76ED395 (user_id), INDEX IDX_E928E0FFCE07E8FF (questionnaire_id), UNIQUE INDEX user_questionnaire_unique (user_id, questionnaire_id, role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, discussion_id INT NOT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, description VARCHAR(4096) DEFAULT NULL, attachment_name VARCHAR(255) DEFAULT NULL, INDEX IDX_9474526C61220EA6 (creator_id), INDEX IDX_9474526CD079F553 (modifier_id), INDEX IDX_9474526C1ADED311 (discussion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE questionnaire (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, geoname_id INT NOT NULL, survey_id INT NOT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, date_observation_start DATETIME NOT NULL, date_observation_end DATETIME NOT NULL, status ENUM(\'new\',\'completed\',\'validated\',\'published\',\'rejected\') NOT NULL COMMENT \'(DC2Type:questionnaire_status)\', comments LONGTEXT NOT NULL, INDEX IDX_7A64DAF61220EA6 (creator_id), INDEX IDX_7A64DAFD079F553 (modifier_id), INDEX IDX_7A64DAF23F5422B (geoname_id), INDEX IDX_7A64DAFB3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, thematic_filter_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, name LONGTEXT NOT NULL, color LONGTEXT DEFAULT NULL, bg_color LONGTEXT DEFAULT NULL, is_thematic TINYINT(1) DEFAULT \'0\' NOT NULL, sorting SMALLINT DEFAULT 0 NOT NULL, is_nsa TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_7FC45F1D61220EA6 (creator_id), INDEX IDX_7FC45F1DD079F553 (modifier_id), INDEX IDX_7FC45F1DA7D75635 (thematic_filter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter_children (filter_id INT NOT NULL, child_filter_id INT NOT NULL, INDEX IDX_9CF8B41AD395B25E (filter_id), INDEX IDX_9CF8B41A2A4D2D2 (child_filter_id), PRIMARY KEY(filter_id, child_filter_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter_summand (filter_id INT NOT NULL, summand_filter_id INT NOT NULL, INDEX IDX_FCC92082D395B25E (filter_id), INDEX IDX_FCC920825161E09A (summand_filter_id), PRIMARY KEY(filter_id, summand_filter_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, name LONGTEXT NOT NULL, code VARCHAR(255) DEFAULT \'\' NOT NULL, is_active TINYINT(1) DEFAULT \'0\' NOT NULL, year NUMERIC(4, 0) DEFAULT NULL, comments LONGTEXT DEFAULT NULL, date_start DATETIME DEFAULT NULL, date_end DATETIME DEFAULT NULL, type ENUM(\'glaas\',\'jmp\',\'nsa\') NOT NULL COMMENT \'(DC2Type:survey_type)\', INDEX IDX_AD5F9BFC61220EA6 (creator_id), INDEX IDX_AD5F9BFCD079F553 (modifier_id), UNIQUE INDEX survey_code_unique (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE part (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, name LONGTEXT NOT NULL, is_total TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_490F70C661220EA6 (creator_id), INDEX IDX_490F70C6D079F553 (modifier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE population (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, geoname_id INT NOT NULL, part_id INT NOT NULL, questionnaire_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, year NUMERIC(4, 0) NOT NULL, population BIGINT NOT NULL, INDEX IDX_B449A00861220EA6 (creator_id), INDEX IDX_B449A008D079F553 (modifier_id), INDEX IDX_B449A00823F5422B (geoname_id), INDEX IDX_B449A0084CE34BEC (part_id), INDEX IDX_B449A008CE07E8FF (questionnaire_id), UNIQUE INDEX population_unique (year, geoname_id, part_id, questionnaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, value_choice_id INT DEFAULT NULL, question_id INT NOT NULL, questionnaire_id INT NOT NULL, value_user_id INT DEFAULT NULL, part_id INT NOT NULL, date_created DATETIME DEFAULT NULL, date_modified DATETIME DEFAULT NULL, value_percent NUMERIC(7, 6) DEFAULT NULL, value_absolute DOUBLE PRECISION DEFAULT NULL, value_text LONGTEXT DEFAULT NULL, quality NUMERIC(3, 2) DEFAULT \'1\' NOT NULL, INDEX IDX_DADD4A2561220EA6 (creator_id), INDEX IDX_DADD4A25D079F553 (modifier_id), INDEX IDX_DADD4A25DC146367 (value_choice_id), INDEX IDX_DADD4A251E27F6BF (question_id), INDEX IDX_DADD4A25CE07E8FF (questionnaire_id), INDEX IDX_DADD4A254CCCA6F5 (value_user_id), INDEX IDX_DADD4A254CE34BEC (part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6A61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6AD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6A727ACA70 FOREIGN KEY (parent_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_permission ADD CONSTRAINT FK_6F7DF886FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D64961220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D64923F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id)');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AA61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AAD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE rule ADD CONSTRAINT FK_46D8ACCC61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE rule ADD CONSTRAINT FK_46D8ACCCD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A661220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A6D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A64CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A6744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A6CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_questionnaire_usage ADD CONSTRAINT FK_F5F3A4A6D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C2661220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C26D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C264CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C26744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C26D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_geoname_usage ADD CONSTRAINT FK_9CF42C2623F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD7361220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD73D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD734CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD73744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD73CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questionnaire_usage ADD CONSTRAINT FK_7FB5DD73A7D75635 FOREIGN KEY (thematic_filter_id) REFERENCES filter (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494ED079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E579F4768 FOREIGN KEY (chapter_id) REFERENCES question (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494ED395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question_part ADD CONSTRAINT FK_17E19D291E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question_part ADD CONSTRAINT FK_17E19D294CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A9261220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A92D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A921E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_set ADD CONSTRAINT FK_1C0A40E61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE filter_set ADD CONSTRAINT FK_1C0A40ED079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE filter_set_filter ADD CONSTRAINT FK_5EC1F853DD05366 FOREIGN KEY (filter_set_id) REFERENCES filter_set (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_set_filter ADD CONSTRAINT FK_5EC1F85D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E261220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E2D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E2D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_filter_set ADD CONSTRAINT FK_A6A762E23DD05366 FOREIGN KEY (filter_set_id) REFERENCES filter_set (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90F61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FD395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE geoname ADD CONSTRAINT FK_EF41727A61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE geoname ADD CONSTRAINT FK_EF41727AD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE geoname_children ADD CONSTRAINT FK_1C1FCE623F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE geoname_children ADD CONSTRAINT FK_1C1FCE6C72ABC40 FOREIGN KEY (child_geoname_id) REFERENCES geoname (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095AD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C161220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FF61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C1ADED311 FOREIGN KEY (discussion_id) REFERENCES discussion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAFD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF23F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAFB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1D61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1DD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1DA7D75635 FOREIGN KEY (thematic_filter_id) REFERENCES filter (id)');
        $this->addSql('ALTER TABLE filter_children ADD CONSTRAINT FK_9CF8B41AD395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_children ADD CONSTRAINT FK_9CF8B41A2A4D2D2 FOREIGN KEY (child_filter_id) REFERENCES filter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_summand ADD CONSTRAINT FK_FCC92082D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE filter_summand ADD CONSTRAINT FK_FCC920825161E09A FOREIGN KEY (summand_filter_id) REFERENCES filter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFCD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE part ADD CONSTRAINT FK_490F70C661220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE part ADD CONSTRAINT FK_490F70C6D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE population ADD CONSTRAINT FK_B449A00861220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE population ADD CONSTRAINT FK_B449A008D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE population ADD CONSTRAINT FK_B449A00823F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE population ADD CONSTRAINT FK_B449A0084CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE population ADD CONSTRAINT FK_B449A008CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A2561220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25D079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25DC146367 FOREIGN KEY (value_choice_id) REFERENCES choice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A254CCCA6F5 FOREIGN KEY (value_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A254CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');

        $this->addSql(<<<STRING
CREATE PROCEDURE cascade_delete_rules_with_references(objectId INT, objectType CHAR)
BEGIN
    DECLARE formulaComponent VARCHAR(100) DEFAULT '(Q\#|F\#|R\#|P\#|S\#|Y[+-]?|\\\\d+|current|all|,)*';
    DECLARE pattern VARCHAR(300);

    IF objectType != 'F' AND objectType != 'Q' AND objectType != 'P' AND objectType != 'R'  THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Given objectType not supported for custom rule integrity checks';
    END IF;

    -- Build regexp pattern to find any reference of the deleted object
    SET pattern = CONCAT('{', formulaComponent, objectType , '#', objectId, '(?!\\\\d)', formulaComponent,'}');

    -- Delete all rules containing a reference to the deleted object
    DELETE FROM rule WHERE formula REGEXP pattern COLLATE utf8_unicode_ci;
END
STRING
        );

        $this->addSql(<<<STRING
CREATE TRIGGER cascade_delete_rules_with_references_to_filters AFTER DELETE ON filter FOR EACH ROW
BEGIN
    CALL cascade_delete_rules_with_references(OLD.id, 'F');
END
STRING
        );
        $this->addSql(<<<STRING
CREATE TRIGGER cascade_delete_rules_with_references_to_questionnaires AFTER DELETE ON questionnaire FOR EACH ROW
BEGIN
    CALL cascade_delete_rules_with_references(OLD.id, 'Q');
END
STRING
        );
        $this->addSql(<<<STRING
CREATE TRIGGER cascade_delete_rules_with_references_to_parts AFTER DELETE ON part FOR EACH ROW
BEGIN
    CALL cascade_delete_rules_with_references(OLD.id, 'P');
END
STRING
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
