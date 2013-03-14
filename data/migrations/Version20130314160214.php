<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130314160214 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE population DROP CONSTRAINT FK_B449A00861220EA6");
        $this->addSql("ALTER TABLE population DROP CONSTRAINT FK_B449A008D079F553");
        $this->addSql("ALTER TABLE population DROP CONSTRAINT FK_B449A008F92F3E70");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A00861220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A008D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT FK_B449A008F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT FK_DADD4A2561220EA6");
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT FK_DADD4A25D079F553");
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT FK_DADD4A251E27F6BF");
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT FK_DADD4A25CE07E8FF");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A2561220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT FK_7A64DAF61220EA6");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT FK_7A64DAFD079F553");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT FK_7A64DAF23F5422B");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT FK_7A64DAFB3FE509D");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAFD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAF23F5422B FOREIGN KEY (geoname_id) REFERENCES geoname (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAFB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT FK_C80D80C161220EA6");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT FK_C80D80C1D079F553");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT FK_C80D80C1A76ED395");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT FK_C80D80C1D60322AC");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT FK_C80D80C1B3FE509D");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C161220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1A76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT FK_C80D80C1B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role DROP CONSTRAINT FK_57698A6A61220EA6");
        $this->addSql("ALTER TABLE role DROP CONSTRAINT FK_57698A6AD079F553");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT FK_57698A6A61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT FK_57698A6AD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE survey DROP CONSTRAINT FK_AD5F9BFC61220EA6");
        $this->addSql("ALTER TABLE survey DROP CONSTRAINT FK_AD5F9BFCD079F553");
        $this->addSql("ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFCD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT FK_64C19C161220EA6");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT FK_64C19C1D079F553");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT FK_64C19C1C78AA2E8");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT FK_64C19C1727ACA70");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT FK_64C19C161220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT FK_64C19C1D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT FK_64C19C1C78AA2E8 FOREIGN KEY (official_category_id) REFERENCES category (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE \"user\" DROP CONSTRAINT FK_8D93D64961220EA6");
        $this->addSql("ALTER TABLE \"user\" DROP CONSTRAINT FK_8D93D649D079F553");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT FK_8D93D64961220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT FK_8D93D649D079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT FK_E928E0FF61220EA6");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT FK_E928E0FFD079F553");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT FK_E928E0FFA76ED395");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT FK_E928E0FFCE07E8FF");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT FK_E928E0FFD60322AC");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FF61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFD079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFA76ED395 FOREIGN KEY (user_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFCE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT FK_E928E0FFD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494E61220EA6");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494ED079F553");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494E12469DE2");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494ECBEBC9B5");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494E727ACA70");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT FK_B6F7494ECE07E8FF");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494E61220EA6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494ED079F553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494ECBEBC9B5 FOREIGN KEY (official_question_id) REFERENCES question (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494E727ACA70 FOREIGN KEY (parent_id) REFERENCES question (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT FK_B6F7494ECE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT fk_dadd4a2561220ea6");
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT fk_dadd4a25d079f553");
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT fk_dadd4a251e27f6bf");
        $this->addSql("ALTER TABLE answer DROP CONSTRAINT fk_dadd4a25ce07e8ff");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT fk_dadd4a2561220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT fk_dadd4a25d079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT fk_dadd4a251e27f6bf FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE answer ADD CONSTRAINT fk_dadd4a25ce07e8ff FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE survey DROP CONSTRAINT fk_ad5f9bfc61220ea6");
        $this->addSql("ALTER TABLE survey DROP CONSTRAINT fk_ad5f9bfcd079f553");
        $this->addSql("ALTER TABLE survey ADD CONSTRAINT fk_ad5f9bfc61220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE survey ADD CONSTRAINT fk_ad5f9bfcd079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT fk_c80d80c161220ea6");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT fk_c80d80c1d079f553");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT fk_c80d80c1a76ed395");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT fk_c80d80c1d60322ac");
        $this->addSql("ALTER TABLE user_survey DROP CONSTRAINT fk_c80d80c1b3fe509d");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT fk_c80d80c161220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT fk_c80d80c1d079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT fk_c80d80c1a76ed395 FOREIGN KEY (user_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT fk_c80d80c1d60322ac FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_survey ADD CONSTRAINT fk_c80d80c1b3fe509d FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE \"user\" DROP CONSTRAINT fk_8d93d64961220ea6");
        $this->addSql("ALTER TABLE \"user\" DROP CONSTRAINT fk_8d93d649d079f553");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT fk_8d93d64961220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE \"user\" ADD CONSTRAINT fk_8d93d649d079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT fk_64c19c161220ea6");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT fk_64c19c1d079f553");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT fk_64c19c1c78aa2e8");
        $this->addSql("ALTER TABLE category DROP CONSTRAINT fk_64c19c1727aca70");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT fk_64c19c161220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT fk_64c19c1d079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT fk_64c19c1c78aa2e8 FOREIGN KEY (official_category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE category ADD CONSTRAINT fk_64c19c1727aca70 FOREIGN KEY (parent_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role DROP CONSTRAINT fk_57698a6a61220ea6");
        $this->addSql("ALTER TABLE role DROP CONSTRAINT fk_57698a6ad079f553");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT fk_57698a6a61220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE role ADD CONSTRAINT fk_57698a6ad079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT fk_e928e0ff61220ea6");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT fk_e928e0ffd079f553");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT fk_e928e0ffa76ed395");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT fk_e928e0ffce07e8ff");
        $this->addSql("ALTER TABLE user_questionnaire DROP CONSTRAINT fk_e928e0ffd60322ac");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT fk_e928e0ff61220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT fk_e928e0ffd079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT fk_e928e0ffa76ed395 FOREIGN KEY (user_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT fk_e928e0ffce07e8ff FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_questionnaire ADD CONSTRAINT fk_e928e0ffd60322ac FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT fk_7a64daf61220ea6");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT fk_7a64dafd079f553");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT fk_7a64daf23f5422b");
        $this->addSql("ALTER TABLE questionnaire DROP CONSTRAINT fk_7a64dafb3fe509d");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT fk_7a64daf61220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT fk_7a64dafd079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT fk_7a64daf23f5422b FOREIGN KEY (geoname_id) REFERENCES geoname (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE questionnaire ADD CONSTRAINT fk_7a64dafb3fe509d FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT fk_b6f7494e61220ea6");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT fk_b6f7494ed079f553");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT fk_b6f7494e12469de2");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT fk_b6f7494ecbebc9b5");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT fk_b6f7494e727aca70");
        $this->addSql("ALTER TABLE question DROP CONSTRAINT fk_b6f7494ece07e8ff");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT fk_b6f7494e61220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT fk_b6f7494ed079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT fk_b6f7494e12469de2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT fk_b6f7494ecbebc9b5 FOREIGN KEY (official_question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT fk_b6f7494e727aca70 FOREIGN KEY (parent_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE question ADD CONSTRAINT fk_b6f7494ece07e8ff FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population DROP CONSTRAINT fk_b449a00861220ea6");
        $this->addSql("ALTER TABLE population DROP CONSTRAINT fk_b449a008d079f553");
        $this->addSql("ALTER TABLE population DROP CONSTRAINT fk_b449a008f92f3e70");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT fk_b449a00861220ea6 FOREIGN KEY (creator_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT fk_b449a008d079f553 FOREIGN KEY (modifier_id) REFERENCES \"user\" (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE population ADD CONSTRAINT fk_b449a008f92f3e70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }
}
