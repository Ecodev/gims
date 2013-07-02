<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Add Palestina to existing countries for demonstration purpose
 * Remember after the migration to refresh the JMP data with:
 * <code>./vendor/bin/doctrine-module migrations:migrate
 * php htdocs/index.php import population
 * php htdocs/index.php import jmp data/cache/Country_data_Asia/Western_Asia/Palestine_12.xlsm</code>
 */
class Version20130702150300 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO country (code, geoname_id, iso3, iso_numeric, fips, name, capital, area, continent, tld, currency_code, currency_name, phone, postal_code_format, postal_code_regexp, languages, neighbors, equivalent_fips_code) VALUES ('PS', 6254930, 'PSE', 275, 'WE', 'Palestinian Territory', 'East Jerusalem', 5970, 'AS', '.ps ', 'ILS', 'Shekel', '970', NULL, NULL, 'ar-PS', 'JO,IL', NULL);");
    }

    public function down(Schema $schema)
    {
        $this->addSql("DELETE FROM country WHERE geoname_id=6254930;");
    }

}
