<?php

/**
 * Check that all existing files on disk exist in the countries array
 * @param array $countries
 */
function check(array $countries)
{
    $files = explode("\n", trim(`find data/cache/country_data -type f`));
    echo count($files) . ' files found on disk' . PHP_EOL;
    echo count($countries) . ' countries known in PHP array' . PHP_EOL;
    foreach ($countries as $a) {
        $k = array_search($a['path'], $files);
        if ($k !== false) {
            unset($files[$k]);
        }
    }
    echo count($files) . ' unkown files on disk' . PHP_EOL;
    var_export($files);
    echo PHP_EOL;
}

/**
 * List of all countries with their ID from database and Excel file
 */
return [
    [
        'id' => 7,
        'iso3' => '',
        'name' => 'MDG - Southern Asia',
        'path' => 'region',
    ],
    [
        'id' => 1149361,
        'iso3' => 'AFG',
        'name' => 'Afghanistan',
        'path' => 'data/cache/country_data/Afghanistan_15.xlsm',
    ],
    [
        'id' => 661882,
        'iso3' => 'ALA',
        'name' => 'Åland Islands',
        'path' => '',
    ],
    [
        'id' => 783754,
        'iso3' => 'ALB',
        'name' => 'Albania',
        'path' => 'data/cache/country_data/Albania_15.xlsm',
    ],
    [
        'id' => 2589581,
        'iso3' => 'DZA',
        'name' => 'Algeria',
        'path' => 'data/cache/country_data/Algeria_15.xlsm',
    ],
    [
        'id' => 5880801,
        'iso3' => 'ASM',
        'name' => 'American Samoa',
        'path' => 'data/cache/country_data/Samoa_american_15.xlsm',
    ],
    [
        'id' => 3041565,
        'iso3' => 'AND',
        'name' => 'Andorra',
        'path' => 'data/cache/country_data/Andorra_15.xlsm',
    ],
    [
        'id' => 3351879,
        'iso3' => 'AGO',
        'name' => 'Angola',
        'path' => 'data/cache/country_data/Angola_15.xlsm',
    ],
    [
        'id' => 3573511,
        'iso3' => 'AIA',
        'name' => 'Anguilla',
        'path' => 'data/cache/country_data/Anguilla_15.xlsm',
    ],
    [
        'id' => 6697173,
        'iso3' => 'ATA',
        'name' => 'Antarctica',
        'path' => '',
    ],
    [
        'id' => 3576396,
        'iso3' => 'ATG',
        'name' => 'Antigua and Barbuda',
        'path' => 'data/cache/country_data/Antigua_and_Barbuda_15.xlsm',
    ],
    [
        'id' => 3865483,
        'iso3' => 'ARG',
        'name' => 'Argentina',
        'path' => 'data/cache/country_data/argentina_15.xlsm',
    ],
    [
        'id' => 174982,
        'iso3' => 'ARM',
        'name' => 'Armenia',
        'path' => 'data/cache/country_data/Armenia_15.xlsm',
    ],
    [
        'id' => 3577279,
        'iso3' => 'ABW',
        'name' => 'Aruba',
        'path' => 'data/cache/country_data/aruba_15.xlsm',
    ],
    [
        'id' => 2077456,
        'iso3' => 'AUS',
        'name' => 'Australia',
        'path' => 'data/cache/country_data/Australia_15.xlsm',
    ],
    [
        'id' => 2782113,
        'iso3' => 'AUT',
        'name' => 'Austria',
        'path' => 'data/cache/country_data/Austria_15.xlsm',
    ],
    [
        'id' => 587116,
        'iso3' => 'AZE',
        'name' => 'Azerbaijan',
        'path' => 'data/cache/country_data/Azerbaijan_15.xlsm',
    ],
    [
        'id' => 3572887,
        'iso3' => 'BHS',
        'name' => 'Bahamas',
        'path' => 'data/cache/country_data/Bahamas_15.xlsm',
    ],
    [
        'id' => 290291,
        'iso3' => 'BHR',
        'name' => 'Bahrain',
        'path' => 'data/cache/country_data/Bahrain_15.xlsm',
    ],
    [
        'id' => 1210997,
        'iso3' => 'BGD',
        'name' => 'Bangladesh',
        'path' => 'data/cache/country_data/Bangladesh_15.xlsm',
    ],
    [
        'id' => 3374084,
        'iso3' => 'BRB',
        'name' => 'Barbados',
        'path' => 'data/cache/country_data/barbados_15.xlsm',
    ],
    [
        'id' => 630336,
        'iso3' => 'BLR',
        'name' => 'Belarus',
        'path' => 'data/cache/country_data/Belarus_15.xlsm',
    ],
    [
        'id' => 2802361,
        'iso3' => 'BEL',
        'name' => 'Belgium',
        'path' => 'data/cache/country_data/Belgium_15.xlsm',
    ],
    [
        'id' => 3582678,
        'iso3' => 'BLZ',
        'name' => 'Belize',
        'path' => 'data/cache/country_data/belize_15.xlsm',
    ],
    [
        'id' => 2395170,
        'iso3' => 'BEN',
        'name' => 'Benin',
        'path' => 'data/cache/country_data/Benin_15.xlsm',
    ],
    [
        'id' => 3573345,
        'iso3' => 'BMU',
        'name' => 'Bermuda',
        'path' => 'data/cache/country_data/Bermuda_15.xlsm',
    ],
    [
        'id' => 1252634,
        'iso3' => 'BTN',
        'name' => 'Bhutan',
        'path' => 'data/cache/country_data/Bhutan_15.xlsm',
    ],
    [
        'id' => 3923057,
        'iso3' => 'BOL',
        'name' => 'Bolivia (Plurinational State of)',
        'path' => 'data/cache/country_data/bolivia_15.xlsm',
    ],
    [
        'id' => 7626844,
        'iso3' => 'BES',
        'name' => 'Bonaire, Saint Eustatius and Saba',
        'path' => '',
    ],
    [
        'id' => 3277605,
        'iso3' => 'BIH',
        'name' => 'Bosnia and Herzegovina',
        'path' => 'data/cache/country_data/Bosnia_herzegovina_15.xlsm',
    ],
    [
        'id' => 933860,
        'iso3' => 'BWA',
        'name' => 'Botswana',
        'path' => 'data/cache/country_data/Botswana_15.xlsm',
    ],
    [
        'id' => 3371123,
        'iso3' => 'BVT',
        'name' => 'Bouvet Island',
        'path' => '',
    ],
    [
        'id' => 3469034,
        'iso3' => 'BRA',
        'name' => 'Brazil',
        'path' => 'data/cache/country_data/brazil_15.xlsm',
    ],
    [
        'id' => 1282588,
        'iso3' => 'IOT',
        'name' => 'British Indian Ocean Territory',
        'path' => '',
    ],
    [
        'id' => 3577718,
        'iso3' => 'VGB',
        'name' => 'British Virgin Islands',
        'path' => 'data/cache/country_data/British_Virgin_Islands_15.xlsm',
    ],
    [
        'id' => 1820814,
        'iso3' => 'BRN',
        'name' => 'Brunei Darussalam',
        'path' => 'data/cache/country_data/Brunei_darussalam_15.xlsm',
    ],
    [
        'id' => 732800,
        'iso3' => 'BGR',
        'name' => 'Bulgaria',
        'path' => 'data/cache/country_data/Bulgaria_15.xlsm',
    ],
    [
        'id' => 2361809,
        'iso3' => 'BFA',
        'name' => 'Burkina Faso',
        'path' => 'data/cache/country_data/Burkina_Faso_15.xlsm',
    ],
    [
        'id' => 433561,
        'iso3' => 'BDI',
        'name' => 'Burundi',
        'path' => 'data/cache/country_data/Burundi_15.xlsm',
    ],
    [
        'id' => 1831722,
        'iso3' => 'KHM',
        'name' => 'Cambodia',
        'path' => 'data/cache/country_data/Cambodia_15.xlsm',
    ],
    [
        'id' => 2233387,
        'iso3' => 'CMR',
        'name' => 'Cameroon',
        'path' => 'data/cache/country_data/Cameroon_15.xlsm',
    ],
    [
        'id' => 6251999,
        'iso3' => 'CAN',
        'name' => 'Canada',
        'path' => 'data/cache/country_data/Canada_15.xlsm',
    ],
    [
        'id' => 3374766,
        'iso3' => 'CPV',
        'name' => 'Cabo Verde',
        'path' => 'data/cache/country_data/Cape_Verde_15.xlsm',
    ],
    [
        'id' => 3580718,
        'iso3' => 'CYM',
        'name' => 'Cayman Islands',
        'path' => 'data/cache/country_data/Cayman_islands_15.xlsm',
    ],
    [
        'id' => 239880,
        'iso3' => 'CAF',
        'name' => 'Central African Republic',
        'path' => 'data/cache/country_data/Central_african_rep_15.xlsm',
    ],
    [
        'id' => 2434508,
        'iso3' => 'TCD',
        'name' => 'Chad',
        'path' => 'data/cache/country_data/Chad_15.xlsm',
    ],
    [
        'id' => 3895114,
        'iso3' => 'CHL',
        'name' => 'Chile',
        'path' => 'data/cache/country_data/chile_15.xlsm',
    ],
    [
        'id' => 1814991,
        'iso3' => 'CHN',
        'name' => 'China',
        'path' => 'data/cache/country_data/China_15.xlsm',
    ],
    [
        'id' => 2078138,
        'iso3' => 'CXR',
        'name' => 'Christmas Island',
        'path' => '',
    ],
    [
        'id' => 1547376,
        'iso3' => 'CCK',
        'name' => 'Cocos Islands',
        'path' => '',
    ],
    [
        'id' => 3686110,
        'iso3' => 'COL',
        'name' => 'Colombia',
        'path' => 'data/cache/country_data/colombia_15.xlsm',
    ],
    [
        'id' => 921929,
        'iso3' => 'COM',
        'name' => 'Comoros',
        'path' => 'data/cache/country_data/Comoros_15.xlsm',
    ],
    [
        'id' => 1899402,
        'iso3' => 'COK',
        'name' => 'Cook Islands',
        'path' => 'data/cache/country_data/Cook_islands_15.xlsm',
    ],
    [
        'id' => 3624060,
        'iso3' => 'CRI',
        'name' => 'Costa Rica',
        'path' => 'data/cache/country_data/costa_rica_15.xlsm',
    ],
    [
        'id' => 3202326,
        'iso3' => 'HRV',
        'name' => 'Croatia',
        'path' => 'data/cache/country_data/Croatia_15.xlsm',
    ],
    [
        'id' => 3562981,
        'iso3' => 'CUB',
        'name' => 'Cuba',
        'path' => 'data/cache/country_data/cuba_15.xlsm',
    ],
    [
        'id' => 7626836,
        'iso3' => 'CUW',
        'name' => 'Curaçao',
        'path' => '',
    ],
    [
        'id' => 146669,
        'iso3' => 'CYP',
        'name' => 'Cyprus',
        'path' => 'data/cache/country_data/Cyprus_15.xlsm',
    ],
    [
        'id' => 3077311,
        'iso3' => 'CZE',
        'name' => 'Czech Republic',
        'path' => 'data/cache/country_data/Czech_rep_15.xlsm',
    ],
    [
        'id' => 203312,
        'iso3' => 'COD',
        'name' => 'Democratic Republic of the Congo',
        'path' => 'data/cache/country_data/Congo_dem_rep_of_15.xlsm',
    ],
    [
        'id' => 2623032,
        'iso3' => 'DNK',
        'name' => 'Denmark',
        'path' => 'data/cache/country_data/Denmark_15.xlsm',
    ],
    [
        'id' => 223816,
        'iso3' => 'DJI',
        'name' => 'Djibouti',
        'path' => 'data/cache/country_data/Djibouti_15.xlsm',
    ],
    [
        'id' => 3575830,
        'iso3' => 'DMA',
        'name' => 'Dominica',
        'path' => 'data/cache/country_data/dominica_15.xlsm',
    ],
    [
        'id' => 3508796,
        'iso3' => 'DOM',
        'name' => 'Dominican Republic',
        'path' => 'data/cache/country_data/dominican_republic_15.xlsm',
    ],
    [
        'id' => 1966436,
        'iso3' => 'TLS',
        'name' => 'Timor-Leste',
        'path' => 'data/cache/country_data/Timor_leste_Dem_rep_of_15.xlsm',
    ],
    [
        'id' => 3658394,
        'iso3' => 'ECU',
        'name' => 'Ecuador',
        'path' => 'data/cache/country_data/ecuador_15.xlsm',
    ],
    [
        'id' => 357994,
        'iso3' => 'EGY',
        'name' => 'Egypt',
        'path' => 'data/cache/country_data/Egypt_15.xlsm',
    ],
    [
        'id' => 3585968,
        'iso3' => 'SLV',
        'name' => 'El Salvador',
        'path' => 'data/cache/country_data/el_salvador_15.xlsm',
    ],
    [
        'id' => 2309096,
        'iso3' => 'GNQ',
        'name' => 'Equatorial Guinea',
        'path' => 'data/cache/country_data/Guinea_equatorial_15.xlsm',
    ],
    [
        'id' => 338010,
        'iso3' => 'ERI',
        'name' => 'Eritrea',
        'path' => 'data/cache/country_data/Eritrea_15.xlsm',
    ],
    [
        'id' => 453733,
        'iso3' => 'EST',
        'name' => 'Estonia',
        'path' => 'data/cache/country_data/Estonia_15.xlsm',
    ],
    [
        'id' => 337996,
        'iso3' => 'ETH',
        'name' => 'Ethiopia',
        'path' => 'data/cache/country_data/Ethiopia_15.xlsm',
    ],
    [
        'id' => 3474414,
        'iso3' => 'FLK',
        'name' => 'Falkland Islands (Malvinas)',
        'path' => 'data/cache/country_data/falkland_islands_15.xlsm',
    ],
    [
        'id' => 2622320,
        'iso3' => 'FRO',
        'name' => 'Faeroe Islands',
        'path' => 'data/cache/country_data/Faeroe_islands_15.xlsm',
    ],
    [
        'id' => 2205218,
        'iso3' => 'FJI',
        'name' => 'Fiji',
        'path' => 'data/cache/country_data/Fiji_15.xlsm',
    ],
    [
        'id' => 660013,
        'iso3' => 'FIN',
        'name' => 'Finland',
        'path' => 'data/cache/country_data/Finland_15.xlsm',
    ],
    [
        'id' => 3017382,
        'iso3' => 'FRA',
        'name' => 'France',
        'path' => 'data/cache/country_data/France_15.xlsm',
    ],
    [
        'id' => 3381670,
        'iso3' => 'GUF',
        'name' => 'French Guiana',
        'path' => 'data/cache/country_data/french_guiana_15.xlsm',
    ],
    [
        'id' => 4030656,
        'iso3' => 'PYF',
        'name' => 'French Polynesia',
        'path' => 'data/cache/country_data/Polynesia_french_15.xlsm',
    ],
    [
        'id' => 1546748,
        'iso3' => 'ATF',
        'name' => 'French Southern Territories',
        'path' => '',
    ],
    [
        'id' => 2400553,
        'iso3' => 'GAB',
        'name' => 'Gabon',
        'path' => 'data/cache/country_data/Gabon_15.xlsm',
    ],
    [
        'id' => 2413451,
        'iso3' => 'GMB',
        'name' => 'Gambia',
        'path' => 'data/cache/country_data/Gambia_15.xlsm',
    ],
    [
        'id' => 614540,
        'iso3' => 'GEO',
        'name' => 'Georgia',
        'path' => 'data/cache/country_data/Georgia_15.xlsm',
    ],
    [
        'id' => 2921044,
        'iso3' => 'DEU',
        'name' => 'Germany',
        'path' => 'data/cache/country_data/Germany_15.xlsm',
    ],
    [
        'id' => 2300660,
        'iso3' => 'GHA',
        'name' => 'Ghana',
        'path' => 'data/cache/country_data/Ghana_15.xlsm',
    ],
    [
        'id' => 2411586,
        'iso3' => 'GIB',
        'name' => 'Gibraltar',
        'path' => 'data/cache/country_data/Gibraltar_15.xlsm',
    ],
    [
        'id' => 390903,
        'iso3' => 'GRC',
        'name' => 'Greece',
        'path' => 'data/cache/country_data/Greece_15.xlsm',
    ],
    [
        'id' => 3425505,
        'iso3' => 'GRL',
        'name' => 'Greenland',
        'path' => 'data/cache/country_data/Greenland_15.xlsm',
    ],
    [
        'id' => 3580239,
        'iso3' => 'GRD',
        'name' => 'Grenada',
        'path' => 'data/cache/country_data/Grenada_15.xlsm',
    ],
    [
        'id' => 3579143,
        'iso3' => 'GLP',
        'name' => 'Guadeloupe',
        'path' => 'data/cache/country_data/guadeloupe_15.xlsm',
    ],
    [
        'id' => 4043988,
        'iso3' => 'GUM',
        'name' => 'Guam',
        'path' => 'data/cache/country_data/Guam_15.xlsm',
    ],
    [
        'id' => 3595528,
        'iso3' => 'GTM',
        'name' => 'Guatemala',
        'path' => 'data/cache/country_data/guatemala_15.xlsm',
    ],
    [
        'id' => 3042400,
        'iso3' => 'CIS',
        'name' => 'Channel Islands',
        'path' => 'data/cache/country_data/Channel_islands_15.xlsm',
    ],
    [
        'id' => 2420477,
        'iso3' => 'GIN',
        'name' => 'Guinea',
        'path' => 'data/cache/country_data/Guinea_15.xlsm',
    ],
    [
        'id' => 2372248,
        'iso3' => 'GNB',
        'name' => 'Guinea-Bissau',
        'path' => 'data/cache/country_data/Guinea_Bissau_15.xlsm',
    ],
    [
        'id' => 3378535,
        'iso3' => 'GUY',
        'name' => 'Guyana',
        'path' => 'data/cache/country_data/guyana_15.xlsm',
    ],
    [
        'id' => 3723988,
        'iso3' => 'HTI',
        'name' => 'Haiti',
        'path' => 'data/cache/country_data/haiti_15.xlsm',
    ],
    [
        'id' => 1547314,
        'iso3' => 'HMD',
        'name' => 'Heard Island and McDonald Islands',
        'path' => '',
    ],
    [
        'id' => 3608932,
        'iso3' => 'HND',
        'name' => 'Honduras',
        'path' => 'data/cache/country_data/honduras_15.xlsm',
    ],
    [
        'id' => 1819730,
        'iso3' => 'HKG',
        'name' => 'China, Hong Kong SAR',
        'path' => 'data/cache/country_data/China_hong_kong_15.xlsm',
    ],
    [
        'id' => 719819,
        'iso3' => 'HUN',
        'name' => 'Hungary',
        'path' => 'data/cache/country_data/Hungary_15.xlsm',
    ],
    [
        'id' => 2629691,
        'iso3' => 'ISL',
        'name' => 'Iceland',
        'path' => 'data/cache/country_data/Iceland_15.xlsm',
    ],
    [
        'id' => 1269750,
        'iso3' => 'IND',
        'name' => 'India',
        'path' => 'data/cache/country_data/India_15.xlsm',
    ],
    [
        'id' => 1643084,
        'iso3' => 'IDN',
        'name' => 'Indonesia',
        'path' => 'data/cache/country_data/Indonesia_15.xlsm',
    ],
    [
        'id' => 130758,
        'iso3' => 'IRN',
        'name' => 'Iran (Islamic Republic of)',
        'path' => 'data/cache/country_data/Iran_islamic_rep_of_15.xlsm',
    ],
    [
        'id' => 99237,
        'iso3' => 'IRQ',
        'name' => 'Iraq',
        'path' => 'data/cache/country_data/Iraq_15.xlsm',
    ],
    [
        'id' => 2963597,
        'iso3' => 'IRL',
        'name' => 'Ireland',
        'path' => 'data/cache/country_data/Ireland_15.xlsm',
    ],
    [
        'id' => 3042225,
        'iso3' => 'IMN',
        'name' => 'Isle of Man',
        'path' => 'data/cache/country_data/Man_isle_of_15.xlsm',
    ],
    [
        'id' => 294640,
        'iso3' => 'ISR',
        'name' => 'Israel',
        'path' => 'data/cache/country_data/Israel_15.xlsm',
    ],
    [
        'id' => 3175395,
        'iso3' => 'ITA',
        'name' => 'Italy',
        'path' => 'data/cache/country_data/Italy_15.xlsm',
    ],
    [
        'id' => 2287781,
        'iso3' => 'CIV',
        'name' => 'Côte d\'Ivoire',
        'path' => 'data/cache/country_data/Cote_d_Ivoire_15.xlsm',
    ],
    [
        'id' => 3489940,
        'iso3' => 'JAM',
        'name' => 'Jamaica',
        'path' => 'data/cache/country_data/jamaica_15.xlsm',
    ],
    [
        'id' => 1861060,
        'iso3' => 'JPN',
        'name' => 'Japan',
        'path' => 'data/cache/country_data/Japan_15.xlsm',
    ],
    [
        'id' => 3042142,
        'iso3' => 'JEY',
        'name' => 'Jersey',
        'path' => '',
    ],
    [
        'id' => 248816,
        'iso3' => 'JOR',
        'name' => 'Jordan',
        'path' => 'data/cache/country_data/Jordan_15.xlsm',
    ],
    [
        'id' => 1522867,
        'iso3' => 'KAZ',
        'name' => 'Kazakhstan',
        'path' => 'data/cache/country_data/Kazakhstan_15.xlsm',
    ],
    [
        'id' => 192950,
        'iso3' => 'KEN',
        'name' => 'Kenya',
        'path' => 'data/cache/country_data/Kenya_15.xlsm',
    ],
    [
        'id' => 4030945,
        'iso3' => 'KIR',
        'name' => 'Kiribati',
        'path' => 'data/cache/country_data/Kiribati_15.xlsm',
    ],
    [
        'id' => 831053,
        'iso3' => 'XKX',
        'name' => 'Kosovo',
        'path' => '',
    ],
    [
        'id' => 285570,
        'iso3' => 'KWT',
        'name' => 'Kuwait',
        'path' => 'data/cache/country_data/Kuwait_15.xlsm',
    ],
    [
        'id' => 1527747,
        'iso3' => 'KGZ',
        'name' => 'Kyrgyzstan',
        'path' => 'data/cache/country_data/Kyrgyzstan_15.xlsm',
    ],
    [
        'id' => 1655842,
        'iso3' => 'LAO',
        'name' => 'Lao People\'s Democratic Republic',
        'path' => 'data/cache/country_data/Lao_people_dem_rep_15.xlsm',
    ],
    [
        'id' => 458258,
        'iso3' => 'LVA',
        'name' => 'Latvia',
        'path' => 'data/cache/country_data/Latvia_15.xlsm',
    ],
    [
        'id' => 272103,
        'iso3' => 'LBN',
        'name' => 'Lebanon',
        'path' => 'data/cache/country_data/Lebanon_15.xlsm',
    ],
    [
        'id' => 932692,
        'iso3' => 'LSO',
        'name' => 'Lesotho',
        'path' => 'data/cache/country_data/Lesotho_15.xlsm',
    ],
    [
        'id' => 2275384,
        'iso3' => 'LBR',
        'name' => 'Liberia',
        'path' => 'data/cache/country_data/Liberia_15.xlsm',
    ],
    [
        'id' => 2215636,
        'iso3' => 'LBY',
        'name' => 'Libyan Arab Jamahiriya',
        'path' => 'data/cache/country_data/Libyan_arab_jamahiriya_15.xlsm',
    ],
    [
        'id' => 3042058,
        'iso3' => 'LIE',
        'name' => 'Liechtenstein',
        'path' => 'data/cache/country_data/Liechtenstein_15.xlsm',
    ],
    [
        'id' => 597427,
        'iso3' => 'LTU',
        'name' => 'Lithuania',
        'path' => 'data/cache/country_data/Lithuania_15.xlsm',
    ],
    [
        'id' => 2960313,
        'iso3' => 'LUX',
        'name' => 'Luxembourg',
        'path' => 'data/cache/country_data/Luxembourg_15.xlsm',
    ],
    [
        'id' => 1821275,
        'iso3' => 'MAC',
        'name' => 'China, Macao SAR',
        'path' => 'data/cache/country_data/Macau_15.xlsm',
    ],
    [
        'id' => 718075,
        'iso3' => 'MKD',
        'name' => 'TFYR Macedonia',
        'path' => 'data/cache/country_data/Macedonia_TFYR_15.xlsm',
    ],
    [
        'id' => 1062947,
        'iso3' => 'MDG',
        'name' => 'Madagascar',
        'path' => 'data/cache/country_data/Madagascar_15.xlsm',
    ],
    [
        'id' => 927384,
        'iso3' => 'MWI',
        'name' => 'Malawi',
        'path' => 'data/cache/country_data/Malawi_15.xlsm',
    ],
    [
        'id' => 1733045,
        'iso3' => 'MYS',
        'name' => 'Malaysia',
        'path' => 'data/cache/country_data/Malaysia_15.xlsm',
    ],
    [
        'id' => 1282028,
        'iso3' => 'MDV',
        'name' => 'Maldives',
        'path' => 'data/cache/country_data/Maldives_15.xlsm',
    ],
    [
        'id' => 2453866,
        'iso3' => 'MLI',
        'name' => 'Mali',
        'path' => 'data/cache/country_data/Mali_15.xlsm',
    ],
    [
        'id' => 2562770,
        'iso3' => 'MLT',
        'name' => 'Malta',
        'path' => 'data/cache/country_data/Malta_15.xlsm',
    ],
    [
        'id' => 2080185,
        'iso3' => 'MHL',
        'name' => 'Marshall Islands',
        'path' => 'data/cache/country_data/Marshall_islands_15.xlsm',
    ],
    [
        'id' => 3570311,
        'iso3' => 'MTQ',
        'name' => 'Martinique',
        'path' => 'data/cache/country_data/martinique_15.xlsm',
    ],
    [
        'id' => 2378080,
        'iso3' => 'MRT',
        'name' => 'Mauritania',
        'path' => 'data/cache/country_data/Mauritania_15.xlsm',
    ],
    [
        'id' => 934292,
        'iso3' => 'MUS',
        'name' => 'Mauritius',
        'path' => 'data/cache/country_data/Mauritius_15.xlsm',
    ],
    [
        'id' => 1024031,
        'iso3' => 'MYT',
        'name' => 'Mayotte',
        'path' => 'data/cache/country_data/Mayotte_15.xlsm',
    ],
    [
        'id' => 3996063,
        'iso3' => 'MEX',
        'name' => 'Mexico',
        'path' => 'data/cache/country_data/mexico_15.xlsm',
    ],
    [
        'id' => 2081918,
        'iso3' => 'FSM',
        'name' => 'Micronesia (Fed. States of)',
        'path' => 'data/cache/country_data/Micronesia_fed_states_of_15.xlsm',
    ],
    [
        'id' => 617790,
        'iso3' => 'MDA',
        'name' => 'Republic of Moldova',
        'path' => 'data/cache/country_data/Moldova_rep_of_15.xlsm',
    ],
    [
        'id' => 2993457,
        'iso3' => 'MCO',
        'name' => 'Monaco',
        'path' => 'data/cache/country_data/Monaco_15.xlsm',
    ],
    [
        'id' => 2029969,
        'iso3' => 'MNG',
        'name' => 'Mongolia',
        'path' => 'data/cache/country_data/Mongolia_15.xlsm',
    ],
    [
        'id' => 3194884,
        'iso3' => 'MNE',
        'name' => 'Montenegro',
        'path' => 'data/cache/country_data/Montenegro_15.xlsm',
    ],
    [
        'id' => 3578097,
        'iso3' => 'MSR',
        'name' => 'Montserrat',
        'path' => 'data/cache/country_data/Montserrat_15.xlsm',
    ],
    [
        'id' => 2542007,
        'iso3' => 'MAR',
        'name' => 'Morocco',
        'path' => 'data/cache/country_data/Morocco_15.xlsm',
    ],
    [
        'id' => 1036973,
        'iso3' => 'MOZ',
        'name' => 'Mozambique',
        'path' => 'data/cache/country_data/Mozambique_15.xlsm',
    ],
    [
        'id' => 1327865,
        'iso3' => 'MMR',
        'name' => 'Myanmar',
        'path' => 'data/cache/country_data/Myanmar_15.xlsm',
    ],
    [
        'id' => 3355338,
        'iso3' => 'NAM',
        'name' => 'Namibia',
        'path' => 'data/cache/country_data/Namibia_15.xlsm',
    ],
    [
        'id' => 2110425,
        'iso3' => 'NRU',
        'name' => 'Nauru',
        'path' => 'data/cache/country_data/Nauru_15.xlsm',
    ],
    [
        'id' => 1282988,
        'iso3' => 'NPL',
        'name' => 'Nepal',
        'path' => 'data/cache/country_data/Nepal_15.xlsm',
    ],
    [
        'id' => 2750405,
        'iso3' => 'NLD',
        'name' => 'Netherlands',
        'path' => 'data/cache/country_data/Netherlands_15.xlsm',
    ],
    [
        'id' => 8505032,
        'iso3' => 'ANT',
        'name' => 'Netherlands Antilles',
        'path' => 'data/cache/country_data/netherlands_antilles_15.xlsm',
    ],
    [
        'id' => 2139685,
        'iso3' => 'NCL',
        'name' => 'New Caledonia',
        'path' => 'data/cache/country_data/New_caledonia_15.xlsm',
    ],
    [
        'id' => 2186224,
        'iso3' => 'NZL',
        'name' => 'New Zealand',
        'path' => 'data/cache/country_data/New_Zealand_15.xlsm',
    ],
    [
        'id' => 3617476,
        'iso3' => 'NIC',
        'name' => 'Nicaragua',
        'path' => 'data/cache/country_data/nicaragua_15.xlsm',
    ],
    [
        'id' => 2440476,
        'iso3' => 'NER',
        'name' => 'Niger',
        'path' => 'data/cache/country_data/Niger_15.xlsm',
    ],
    [
        'id' => 2328926,
        'iso3' => 'NGA',
        'name' => 'Nigeria',
        'path' => 'data/cache/country_data/Nigeria_15.xlsm',
    ],
    [
        'id' => 4036232,
        'iso3' => 'NIU',
        'name' => 'Niue',
        'path' => 'data/cache/country_data/Niue_15.xlsm',
    ],
    [
        'id' => 2155115,
        'iso3' => 'NFK',
        'name' => 'Norfolk Island',
        'path' => '',
    ],
    [
        'id' => 4041468,
        'iso3' => 'MNP',
        'name' => 'Northern Mariana Islands',
        'path' => 'data/cache/country_data/Mariana_islands_northern_15.xlsm',
    ],
    [
        'id' => 1873107,
        'iso3' => 'PRK',
        'name' => 'Dem. People\'s Republic of Korea',
        'path' => 'data/cache/country_data/Korea_dem_peoples_rep_of_15.xlsm',
    ],
    [
        'id' => 3144096,
        'iso3' => 'NOR',
        'name' => 'Norway',
        'path' => 'data/cache/country_data/Norway_15.xlsm',
    ],
    [
        'id' => 286963,
        'iso3' => 'OMN',
        'name' => 'Oman',
        'path' => 'data/cache/country_data/Oman_15.xlsm',
    ],
    [
        'id' => 1168579,
        'iso3' => 'PAK',
        'name' => 'Pakistan',
        'path' => 'data/cache/country_data/Pakistan_15.xlsm',
    ],
    [
        'id' => 1559582,
        'iso3' => 'PLW',
        'name' => 'Palau',
        'path' => 'data/cache/country_data/Palau_15.xlsm',
    ],
    [
        'id' => 6254930,
        'iso3' => 'PSE',
        'name' => 'West Bank and Gaza Strip',
        'path' => 'data/cache/country_data/Palestine_15.xlsm',
    ],
    [
        'id' => 3703430,
        'iso3' => 'PAN',
        'name' => 'Panama',
        'path' => 'data/cache/country_data/panama_15.xlsm',
    ],
    [
        'id' => 2088628,
        'iso3' => 'PNG',
        'name' => 'Papua New Guinea',
        'path' => 'data/cache/country_data/Papua_new_guinea_15.xlsm',
    ],
    [
        'id' => 3437598,
        'iso3' => 'PRY',
        'name' => 'Paraguay',
        'path' => 'data/cache/country_data/paraguay_15.xlsm',
    ],
    [
        'id' => 3932488,
        'iso3' => 'PER',
        'name' => 'Peru',
        'path' => 'data/cache/country_data/peru_15.xlsm',
    ],
    [
        'id' => 1694008,
        'iso3' => 'PHL',
        'name' => 'Philippines',
        'path' => 'data/cache/country_data/Philippines_15.xlsm',
    ],
    [
        'id' => 4030699,
        'iso3' => 'PCN',
        'name' => 'Pitcairn',
        'path' => '',
    ],
    [
        'id' => 798544,
        'iso3' => 'POL',
        'name' => 'Poland',
        'path' => 'data/cache/country_data/Poland_15.xlsm',
    ],
    [
        'id' => 2264397,
        'iso3' => 'PRT',
        'name' => 'Portugal',
        'path' => 'data/cache/country_data/Portugal_15.xlsm',
    ],
    [
        'id' => 4566966,
        'iso3' => 'PRI',
        'name' => 'Puerto Rico',
        'path' => 'data/cache/country_data/puerto_rico_15.xlsm',
    ],
    [
        'id' => 289688,
        'iso3' => 'QAT',
        'name' => 'Qatar',
        'path' => 'data/cache/country_data/Qatar_15.xlsm',
    ],
    [
        'id' => 2260494,
        'iso3' => 'COG',
        'name' => 'Congo',
        'path' => 'data/cache/country_data/Congo_15.xlsm',
    ],
    [
        'id' => 935317,
        'iso3' => 'REU',
        'name' => 'Réunion',
        'path' => 'data/cache/country_data/Reunion_15.xlsm',
    ],
    [
        'id' => 798549,
        'iso3' => 'ROU',
        'name' => 'Romania',
        'path' => 'data/cache/country_data/Romania_15.xlsm',
    ],
    [
        'id' => 2017370,
        'iso3' => 'RUS',
        'name' => 'Russian Federation',
        'path' => 'data/cache/country_data/Russian_fed_15.xlsm',
    ],
    [
        'id' => 49518,
        'iso3' => 'RWA',
        'name' => 'Rwanda',
        'path' => 'data/cache/country_data/Rwanda_15.xlsm',
    ],
    [
        'id' => 3578476,
        'iso3' => 'BLM',
        'name' => 'Saint-Barthélemy',
        'path' => '',
    ],
    [
        'id' => 3370751,
        'iso3' => 'SHN',
        'name' => 'Saint Helena',
        'path' => 'data/cache/country_data/Saint Helena_15.xlsm',
    ],
    [
        'id' => 3575174,
        'iso3' => 'KNA',
        'name' => 'Saint Kitts and Nevis',
        'path' => 'data/cache/country_data/saint_kitts_and_nevis_15.xlsm',
    ],
    [
        'id' => 3576468,
        'iso3' => 'LCA',
        'name' => 'Saint Lucia',
        'path' => 'data/cache/country_data/saint_lucia_15.xlsm',
    ],
    [
        'id' => 3578421,
        'iso3' => 'MAF',
        'name' => 'Saint-Martin (French part)',
        'path' => '',
    ],
    [
        'id' => 3424932,
        'iso3' => 'SPM',
        'name' => 'Saint Pierre and Miquelon',
        'path' => 'data/cache/country_data/St_Pierre_and_Miquelon_15.xlsm',
    ],
    [
        'id' => 3577815,
        'iso3' => 'VCT',
        'name' => 'Saint Vincent and the Grenadines',
        'path' => 'data/cache/country_data/st_vincent_and_grenad_15.xlsm',
    ],
    [
        'id' => 4034894,
        'iso3' => 'WSM',
        'name' => 'Samoa',
        'path' => 'data/cache/country_data/Samoa_15.xlsm',
    ],
    [
        'id' => 3168068,
        'iso3' => 'SMR',
        'name' => 'San Marino',
        'path' => 'data/cache/country_data/San_marino_15.xlsm',
    ],
    [
        'id' => 2410758,
        'iso3' => 'STP',
        'name' => 'Sao Tome and Principe',
        'path' => 'data/cache/country_data/Sao_tome_and_principe_15.xlsm',
    ],
    [
        'id' => 102358,
        'iso3' => 'SAU',
        'name' => 'Saudi Arabia',
        'path' => 'data/cache/country_data/Saudi_arabia_15.xlsm',
    ],
    [
        'id' => 2245662,
        'iso3' => 'SEN',
        'name' => 'Senegal',
        'path' => 'data/cache/country_data/Senegal_15.xlsm',
    ],
    [
        'id' => 6290252,
        'iso3' => 'SRB',
        'name' => 'Serbia',
        'path' => 'data/cache/country_data/Serbia_15.xlsm',
    ],
    [
        'id' => 241170,
        'iso3' => 'SYC',
        'name' => 'Seychelles',
        'path' => 'data/cache/country_data/Seychelles_15.xlsm',
    ],
    [
        'id' => 2403846,
        'iso3' => 'SLE',
        'name' => 'Sierra Leone',
        'path' => 'data/cache/country_data/Sierra_Leone_15.xlsm',
    ],
    [
        'id' => 1880251,
        'iso3' => 'SGP',
        'name' => 'Singapore',
        'path' => 'data/cache/country_data/Singapore_15.xlsm',
    ],
    [
        'id' => 7609695,
        'iso3' => 'SXM',
        'name' => 'Sint Maarten (Dutch part)',
        'path' => '',
    ],
    [
        'id' => 3057568,
        'iso3' => 'SVK',
        'name' => 'Slovakia',
        'path' => 'data/cache/country_data/Slovakia_15.xlsm',
    ],
    [
        'id' => 3190538,
        'iso3' => 'SVN',
        'name' => 'Slovenia',
        'path' => 'data/cache/country_data/Slovenia_15.xlsm',
    ],
    [
        'id' => 2103350,
        'iso3' => 'SLB',
        'name' => 'Solomon Islands',
        'path' => 'data/cache/country_data/Solomon_islands_15.xlsm',
    ],
    [
        'id' => 51537,
        'iso3' => 'SOM',
        'name' => 'Somalia',
        'path' => 'data/cache/country_data/Somalia_15.xlsm',
    ],
    [
        'id' => 953987,
        'iso3' => 'ZAF',
        'name' => 'South Africa',
        'path' => 'data/cache/country_data/South_Africa_15.xlsm',
    ],
    [
        'id' => 3474415,
        'iso3' => 'SGS',
        'name' => 'South Georgia and the South Sandwich Islands',
        'path' => '',
    ],
    [
        'id' => 1835841,
        'iso3' => 'KOR',
        'name' => 'Republic of Korea',
        'path' => 'data/cache/country_data/Korea_rep_of_15.xlsm',
    ],
    [
        'id' => 7909807,
        'iso3' => 'SSD',
        'name' => 'South Sudan',
        'path' => 'data/cache/country_data/South_Sudan_15.xlsm',
    ],
    [
        'id' => 2510769,
        'iso3' => 'ESP',
        'name' => 'Spain',
        'path' => 'data/cache/country_data/Spain_15.xlsm',
    ],
    [
        'id' => 1227603,
        'iso3' => 'LKA',
        'name' => 'Sri Lanka',
        'path' => 'data/cache/country_data/Sri_lanka_15.xlsm',
    ],
    [
        'id' => 366755,
        'iso3' => 'SDN',
        'name' => 'Sudan',
        'path' => 'data/cache/country_data/Sudan_15.xlsm',
    ],
    [
        'id' => 3382998,
        'iso3' => 'SUR',
        'name' => 'Suriname',
        'path' => 'data/cache/country_data/suriname_15.xlsm',
    ],
    [
        'id' => 607072,
        'iso3' => 'SJM',
        'name' => 'Svalbard and Jan Mayen Islands',
        'path' => '',
    ],
    [
        'id' => 934841,
        'iso3' => 'SWZ',
        'name' => 'Swaziland',
        'path' => 'data/cache/country_data/Swaziland_15.xlsm',
    ],
    [
        'id' => 2661886,
        'iso3' => 'SWE',
        'name' => 'Sweden',
        'path' => 'data/cache/country_data/Sweden_15.xlsm',
    ],
    [
        'id' => 2658434,
        'iso3' => 'CHE',
        'name' => 'Switzerland',
        'path' => 'data/cache/country_data/Switzerland_15.xlsm',
    ],
    [
        'id' => 163843,
        'iso3' => 'SYR',
        'name' => 'Syrian Arab Republic',
        'path' => 'data/cache/country_data/Syrian_arab_rep_15.xlsm',
    ],
    [
        'id' => 1668284,
        'iso3' => 'TWN',
        'name' => 'Taiwan',
        'path' => '',
    ],
    [
        'id' => 1220409,
        'iso3' => 'TJK',
        'name' => 'Tajikistan',
        'path' => 'data/cache/country_data/Tajikistan_15.xlsm',
    ],
    [
        'id' => 149590,
        'iso3' => 'TZA',
        'name' => 'United Republic of Tanzania',
        'path' => 'data/cache/country_data/Tanzania_united_rep_of_15.xlsm',
    ],
    [
        'id' => 1605651,
        'iso3' => 'THA',
        'name' => 'Thailand',
        'path' => 'data/cache/country_data/Thailand_15.xlsm',
    ],
    [
        'id' => 2363686,
        'iso3' => 'TGO',
        'name' => 'Togo',
        'path' => 'data/cache/country_data/Togo_15.xlsm',
    ],
    [
        'id' => 4031074,
        'iso3' => 'TKL',
        'name' => 'Tokelau',
        'path' => 'data/cache/country_data/Tokelau_15.xlsm',
    ],
    [
        'id' => 4032283,
        'iso3' => 'TON',
        'name' => 'Tonga',
        'path' => 'data/cache/country_data/Tonga_15.xlsm',
    ],
    [
        'id' => 3573591,
        'iso3' => 'TTO',
        'name' => 'Trinidad and Tobago',
        'path' => 'data/cache/country_data/Trinidad_and_Tobago_15.xlsm',
    ],
    [
        'id' => 2464461,
        'iso3' => 'TUN',
        'name' => 'Tunisia',
        'path' => 'data/cache/country_data/Tunisia_15.xlsm',
    ],
    [
        'id' => 298795,
        'iso3' => 'TUR',
        'name' => 'Turkey',
        'path' => 'data/cache/country_data/Turkey_15.xlsm',
    ],
    [
        'id' => 1218197,
        'iso3' => 'TKM',
        'name' => 'Turkmenistan',
        'path' => 'data/cache/country_data/Turkmenistan_15.xlsm',
    ],
    [
        'id' => 3576916,
        'iso3' => 'TCA',
        'name' => 'Turks and Caicos Islands',
        'path' => 'data/cache/country_data/Turks_and_Caicos_Islands_15.xlsm',
    ],
    [
        'id' => 2110297,
        'iso3' => 'TUV',
        'name' => 'Tuvalu',
        'path' => 'data/cache/country_data/Tuvalu_15.xlsm',
    ],
    [
        'id' => 226074,
        'iso3' => 'UGA',
        'name' => 'Uganda',
        'path' => 'data/cache/country_data/Uganda_15.xlsm',
    ],
    [
        'id' => 690791,
        'iso3' => 'UKR',
        'name' => 'Ukraine',
        'path' => 'data/cache/country_data/Ukraine_15.xlsm',
    ],
    [
        'id' => 290557,
        'iso3' => 'ARE',
        'name' => 'United Arab Emirates',
        'path' => 'data/cache/country_data/Arab_emirates_united_15.xlsm',
    ],
    [
        'id' => 2635167,
        'iso3' => 'GBR',
        'name' => 'United Kingdom',
        'path' => 'data/cache/country_data/United_kingdom_15.xlsm',
    ],
    [
        'id' => 6252001,
        'iso3' => 'USA',
        'name' => 'United States of America',
        'path' => 'data/cache/country_data/America_united_states_of_15.xlsm',
    ],
    [
        'id' => 5854968,
        'iso3' => 'UMI',
        'name' => 'United States Minor Outlying Islands',
        'path' => '',
    ],
    [
        'id' => 3439705,
        'iso3' => 'URY',
        'name' => 'Uruguay',
        'path' => 'data/cache/country_data/uruguay_15.xlsm',
    ],
    [
        'id' => 4796775,
        'iso3' => 'VIR',
        'name' => 'United States Virgin Islands',
        'path' => 'data/cache/country_data/united_states_virgin_islands_15.xlsm',
    ],
    [
        'id' => 1512440,
        'iso3' => 'UZB',
        'name' => 'Uzbekistan',
        'path' => 'data/cache/country_data/Uzbekistan_15.xlsm',
    ],
    [
        'id' => 2134431,
        'iso3' => 'VUT',
        'name' => 'Vanuatu',
        'path' => 'data/cache/country_data/Vanuatu_15.xlsm',
    ],
    [
        'id' => 3164670,
        'iso3' => 'VAT',
        'name' => 'Holy See',
        'path' => 'data/cache/country_data/Holy_see_15.xlsm',
    ],
    [
        'id' => 3625428,
        'iso3' => 'VEN',
        'name' => 'Venezuela (Bolivarian Republic of)',
        'path' => 'data/cache/country_data/venezuela_15.xlsm',
    ],
    [
        'id' => 1562822,
        'iso3' => 'VNM',
        'name' => 'Viet Nam',
        'path' => 'data/cache/country_data/Viet_Nam_15.xlsm',
    ],
    [
        'id' => 4034749,
        'iso3' => 'WLF',
        'name' => 'Wallis and Futuna Islands',
        'path' => '',
    ],
    [
        'id' => 2461445,
        'iso3' => 'ESH',
        'name' => 'Western Sahara',
        'path' => 'data/cache/country_data/Sahara_western_15.xlsm',
    ],
    [
        'id' => 69543,
        'iso3' => 'YEM',
        'name' => 'Yemen',
        'path' => 'data/cache/country_data/Yemen_15.xlsm',
    ],
    [
        'id' => 895949,
        'iso3' => 'ZMB',
        'name' => 'Zambia',
        'path' => 'data/cache/country_data/Zambia_15.xlsm',
    ],
    [
        'id' => 878675,
        'iso3' => 'ZWE',
        'name' => 'Zimbabwe',
        'path' => 'data/cache/country_data/Zimbabwe_15.xlsm',
    ],
];
