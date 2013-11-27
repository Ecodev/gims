<?php

/**
 * Check that all existing files on disk exist in the countries array
 * @param array $countries
 */
function check(array $countries)
{

    $files = explode("\n", trim(`find data/cache/country_data -type f`));
    echo count($files) . PHP_EOL;
    foreach ($countries as $a) {
        $k = array_search($a['path'], $files);
        if ($k !== false) {
            unset($files[$k]);
        }
    }
    echo count($files) . PHP_EOL;
    var_export($files);
}

/**
 * List of all countries with their ID from database and Excel file
 */
return array(
    0 =>
    array(
        'id' => 3,
        'iso3' => 'AFG',
        'name' => 'Afghanistan',
        'path' => 'data/cache/country_data/Afghanistan_12.xlsm',
    ),
    1 =>
    array(
        'id' => 15,
        'iso3' => 'ALA',
        'name' => 'Aland Islands',
        'path' => '',
    ),
    2 =>
    array(
        'id' => 6,
        'iso3' => 'ALB',
        'name' => 'Albania',
        'path' => 'data/cache/country_data/Albania_12.xlsm',
    ),
    3 =>
    array(
        'id' => 62,
        'iso3' => 'DZA',
        'name' => 'Algeria',
        'path' => 'data/cache/country_data/Algeria_12.xlsm',
    ),
    4 =>
    array(
        'id' => 11,
        'iso3' => 'ASM',
        'name' => 'American Samoa',
        'path' => 'data/cache/country_data/Samoa_american_12.xlsm',
    ),
    5 =>
    array(
        'id' => 1,
        'iso3' => 'AND',
        'name' => 'Andorra',
        'path' => 'data/cache/country_data/Andorra_12.xlsm',
    ),
    6 =>
    array(
        'id' => 8,
        'iso3' => 'AGO',
        'name' => 'Angola',
        'path' => 'data/cache/country_data/Angola_12.xlsm',
    ),
    7 =>
    array(
        'id' => 5,
        'iso3' => 'AIA',
        'name' => 'Anguilla',
        'path' => 'data/cache/country_data/Anguilla_12.xlsm',
    ),
    8 =>
    array(
        'id' => 9,
        'iso3' => 'ATA',
        'name' => 'Antarctica',
        'path' => '',
    ),
    9 =>
    array(
        'id' => 4,
        'iso3' => 'ATG',
        'name' => 'Antigua and Barbuda',
        'path' => 'data/cache/country_data/Antigua_and_Barbuda_12.xlsm',
    ),
    10 =>
    array(
        'id' => 10,
        'iso3' => 'ARG',
        'name' => 'Argentina',
        'path' => 'data/cache/country_data/argentina_12.xlsm',
    ),
    11 =>
    array(
        'id' => 7,
        'iso3' => 'ARM',
        'name' => 'Armenia',
        'path' => 'data/cache/country_data/Armenia_12.xlsm',
    ),
    12 =>
    array(
        'id' => 14,
        'iso3' => 'ABW',
        'name' => 'Aruba',
        'path' => 'data/cache/country_data/aruba_12.xlsm',
    ),
    13 =>
    array(
        'id' => 13,
        'iso3' => 'AUS',
        'name' => 'Australia',
        'path' => 'data/cache/country_data/Australia_12.xlsm',
    ),
    14 =>
    array(
        'id' => 12,
        'iso3' => 'AUT',
        'name' => 'Austria',
        'path' => 'data/cache/country_data/Austria_12.xlsm',
    ),
    15 =>
    array(
        'id' => 16,
        'iso3' => 'AZE',
        'name' => 'Azerbaijan',
        'path' => 'data/cache/country_data/Azerbaijan_12.xlsm',
    ),
    16 =>
    array(
        'id' => 32,
        'iso3' => 'BHS',
        'name' => 'Bahamas',
        'path' => 'data/cache/country_data/Bahamas_12.xlsm',
    ),
    17 =>
    array(
        'id' => 23,
        'iso3' => 'BHR',
        'name' => 'Bahrain',
        'path' => 'data/cache/country_data/Bahrain_12.xlsm',
    ),
    18 =>
    array(
        'id' => 19,
        'iso3' => 'BGD',
        'name' => 'Bangladesh',
        'path' => 'data/cache/country_data/Bangladesh_12.xlsm',
    ),
    19 =>
    array(
        'id' => 18,
        'iso3' => 'BRB',
        'name' => 'Barbados',
        'path' => 'data/cache/country_data/barbados_12.xlsm',
    ),
    20 =>
    array(
        'id' => 36,
        'iso3' => 'BLR',
        'name' => 'Belarus',
        'path' => 'data/cache/country_data/Belarus_12.xlsm',
    ),
    21 =>
    array(
        'id' => 20,
        'iso3' => 'BEL',
        'name' => 'Belgium',
        'path' => 'data/cache/country_data/Belgium_12.xlsm',
    ),
    22 =>
    array(
        'id' => 37,
        'iso3' => 'BLZ',
        'name' => 'Belize',
        'path' => 'data/cache/country_data/belize_12.xlsm',
    ),
    23 =>
    array(
        'id' => 25,
        'iso3' => 'BEN',
        'name' => 'Benin',
        'path' => 'data/cache/country_data/Benin_12.xlsm',
    ),
    24 =>
    array(
        'id' => 27,
        'iso3' => 'BMU',
        'name' => 'Bermuda',
        'path' => 'data/cache/country_data/Bermuda_12.xlsm',
    ),
    25 =>
    array(
        'id' => 33,
        'iso3' => 'BTN',
        'name' => 'Bhutan',
        'path' => 'data/cache/country_data/Bhutan_12.xlsm',
    ),
    26 =>
    array(
        'id' => 29,
        'iso3' => 'BOL',
        'name' => 'Bolivia',
        'path' => 'data/cache/country_data/bolivia_12.xlsm',
    ),
    27 =>
    array(
        'id' => 30,
        'iso3' => 'BES',
        'name' => 'Bonaire, Saint Eustatius and Saba ',
        'path' => '',
    ),
    28 =>
    array(
        'id' => 17,
        'iso3' => 'BIH',
        'name' => 'Bosnia and Herzegovina',
        'path' => 'data/cache/country_data/Bosnia_herzegovina_12.xlsm',
    ),
    29 =>
    array(
        'id' => 35,
        'iso3' => 'BWA',
        'name' => 'Botswana',
        'path' => 'data/cache/country_data/Botswana_12.xlsm',
    ),
    30 =>
    array(
        'id' => 34,
        'iso3' => 'BVT',
        'name' => 'Bouvet Island',
        'path' => '',
    ),
    31 =>
    array(
        'id' => 31,
        'iso3' => 'BRA',
        'name' => 'Brazil',
        'path' => 'data/cache/country_data/brazil_12.xlsm',
    ),
    32 =>
    array(
        'id' => 106,
        'iso3' => 'IOT',
        'name' => 'British Indian Ocean Territory',
        'path' => '',
    ),
    33 =>
    array(
        'id' => 240,
        'iso3' => 'VGB',
        'name' => 'British Virgin Islands',
        'path' => 'data/cache/country_data/British_Virgin_Islands_12.xlsm',
    ),
    34 =>
    array(
        'id' => 28,
        'iso3' => 'BRN',
        'name' => 'Brunei',
        'path' => 'data/cache/country_data/Brunei_darussalam_12.xlsm',
    ),
    35 =>
    array(
        'id' => 22,
        'iso3' => 'BGR',
        'name' => 'Bulgaria',
        'path' => 'data/cache/country_data/Bulgaria_12.xlsm',
    ),
    36 =>
    array(
        'id' => 21,
        'iso3' => 'BFA',
        'name' => 'Burkina Faso',
        'path' => 'data/cache/country_data/Burkina_Faso_12.xlsm',
    ),
    37 =>
    array(
        'id' => 24,
        'iso3' => 'BDI',
        'name' => 'Burundi',
        'path' => 'data/cache/country_data/Burundi_12.xlsm',
    ),
    38 =>
    array(
        'id' => 117,
        'iso3' => 'KHM',
        'name' => 'Cambodia',
        'path' => 'data/cache/country_data/Cambodia_12.xlsm',
    ),
    39 =>
    array(
        'id' => 47,
        'iso3' => 'CMR',
        'name' => 'Cameroon',
        'path' => 'data/cache/country_data/Cameroon_12.xlsm',
    ),
    40 =>
    array(
        'id' => 38,
        'iso3' => 'CAN',
        'name' => 'Canada',
        'path' => 'data/cache/country_data/Canada_12.xlsm',
    ),
    41 =>
    array(
        'id' => 52,
        'iso3' => 'CPV',
        'name' => 'Cape Verde',
        'path' => 'data/cache/country_data/Cape_Verde_12.xlsm',
    ),
    42 =>
    array(
        'id' => 125,
        'iso3' => 'CYM',
        'name' => 'Cayman Islands',
        'path' => 'data/cache/country_data/Cayman_islands_12.xlsm',
    ),
    43 =>
    array(
        'id' => 41,
        'iso3' => 'CAF',
        'name' => 'Central African Republic',
        'path' => 'data/cache/country_data/Central_african_rep_12.xlsm',
    ),
    44 =>
    array(
        'id' => 216,
        'iso3' => 'TCD',
        'name' => 'Chad',
        'path' => 'data/cache/country_data/Chad_12.xlsm',
    ),
    45 =>
    array(
        'id' => 46,
        'iso3' => 'CHL',
        'name' => 'Chile',
        'path' => 'data/cache/country_data/chile_12.xlsm',
    ),
    46 =>
    array(
        'id' => 48,
        'iso3' => 'CHN',
        'name' => 'China',
        'path' => 'data/cache/country_data/China_12.xlsm',
    ),
    47 =>
    array(
        'id' => 54,
        'iso3' => 'CXR',
        'name' => 'Christmas Island',
        'path' => '',
    ),
    48 =>
    array(
        'id' => 39,
        'iso3' => 'CCK',
        'name' => 'Cocos Islands',
        'path' => '',
    ),
    49 =>
    array(
        'id' => 49,
        'iso3' => 'COL',
        'name' => 'Colombia',
        'path' => 'data/cache/country_data/colombia_12.xlsm',
    ),
    50 =>
    array(
        'id' => 119,
        'iso3' => 'COM',
        'name' => 'Comoros',
        'path' => 'data/cache/country_data/Comoros_12.xlsm',
    ),
    51 =>
    array(
        'id' => 45,
        'iso3' => 'COK',
        'name' => 'Cook Islands',
        'path' => 'data/cache/country_data/Cook_islands_12.xlsm',
    ),
    52 =>
    array(
        'id' => 50,
        'iso3' => 'CRI',
        'name' => 'Costa Rica',
        'path' => 'data/cache/country_data/costa_rica_12.xlsm',
    ),
    53 =>
    array(
        'id' => 98,
        'iso3' => 'HRV',
        'name' => 'Croatia',
        'path' => 'data/cache/country_data/Croatia_12.xlsm',
    ),
    54 =>
    array(
        'id' => 51,
        'iso3' => 'CUB',
        'name' => 'Cuba',
        'path' => 'data/cache/country_data/cuba_12.xlsm',
    ),
    55 =>
    array(
        'id' => 53,
        'iso3' => 'CUW',
        'name' => 'Curacao',
        'path' => '',
    ),
    56 =>
    array(
        'id' => 55,
        'iso3' => 'CYP',
        'name' => 'Cyprus',
        'path' => 'data/cache/country_data/Cyprus_12.xlsm',
    ),
    57 =>
    array(
        'id' => 56,
        'iso3' => 'CZE',
        'name' => 'Czech Republic',
        'path' => 'data/cache/country_data/Czech_rep_12.xlsm',
    ),
    58 =>
    array(
        'id' => 40,
        'iso3' => 'COD',
        'name' => 'Democratic Republic of the Congo',
        'path' => 'data/cache/country_data/Congo_dem_rep_of_12.xlsm',
    ),
    59 =>
    array(
        'id' => 59,
        'iso3' => 'DNK',
        'name' => 'Denmark',
        'path' => 'data/cache/country_data/Denmark_12.xlsm',
    ),
    60 =>
    array(
        'id' => 58,
        'iso3' => 'DJI',
        'name' => 'Djibouti',
        'path' => 'data/cache/country_data/Djibouti_12.xlsm',
    ),
    61 =>
    array(
        'id' => 60,
        'iso3' => 'DMA',
        'name' => 'Dominica',
        'path' => 'data/cache/country_data/dominica_12.xlsm',
    ),
    62 =>
    array(
        'id' => 61,
        'iso3' => 'DOM',
        'name' => 'Dominican Republic',
        'path' => 'data/cache/country_data/dominican_republic_12.xlsm',
    ),
    63 =>
    array(
        'id' => 222,
        'iso3' => 'TLS',
        'name' => 'East Timor',
        'path' => 'data/cache/country_data/Timor_leste_Dem_rep_of_12.xlsm',
    ),
    64 =>
    array(
        'id' => 63,
        'iso3' => 'ECU',
        'name' => 'Ecuador',
        'path' => 'data/cache/country_data/ecuador_12.xlsm',
    ),
    65 =>
    array(
        'id' => 65,
        'iso3' => 'EGY',
        'name' => 'Egypt',
        'path' => 'data/cache/country_data/Egypt_12.xlsm',
    ),
    66 =>
    array(
        'id' => 211,
        'iso3' => 'SLV',
        'name' => 'El Salvador',
        'path' => 'data/cache/country_data/el_salvador_12.xlsm',
    ),
    67 =>
    array(
        'id' => 88,
        'iso3' => 'GNQ',
        'name' => 'Equatorial Guinea',
        'path' => 'data/cache/country_data/Guinea_equatorial_12.xlsm',
    ),
    68 =>
    array(
        'id' => 67,
        'iso3' => 'ERI',
        'name' => 'Eritrea',
        'path' => 'data/cache/country_data/Eritrea_12.xlsm',
    ),
    69 =>
    array(
        'id' => 64,
        'iso3' => 'EST',
        'name' => 'Estonia',
        'path' => 'data/cache/country_data/Estonia_12.xlsm',
    ),
    70 =>
    array(
        'id' => 69,
        'iso3' => 'ETH',
        'name' => 'Ethiopia',
        'path' => 'data/cache/country_data/Ethiopia_12.xlsm',
    ),
    71 =>
    array(
        'id' => 72,
        'iso3' => 'FLK',
        'name' => 'Falkland Islands',
        'path' => 'data/cache/country_data/falkland_islands_12.xlsm',
    ),
    72 =>
    array(
        'id' => 74,
        'iso3' => 'FRO',
        'name' => 'Faroe Islands',
        'path' => 'data/cache/country_data/Faeroe_islands_12.xlsm',
    ),
    73 =>
    array(
        'id' => 71,
        'iso3' => 'FJI',
        'name' => 'Fiji',
        'path' => 'data/cache/country_data/Fiji_12.xlsm',
    ),
    74 =>
    array(
        'id' => 70,
        'iso3' => 'FIN',
        'name' => 'Finland',
        'path' => 'data/cache/country_data/Finland_12.xlsm',
    ),
    75 =>
    array(
        'id' => 75,
        'iso3' => 'FRA',
        'name' => 'France',
        'path' => 'data/cache/country_data/France_12.xlsm',
    ),
    76 =>
    array(
        'id' => 80,
        'iso3' => 'GUF',
        'name' => 'French Guiana',
        'path' => 'data/cache/country_data/french_guiana_12.xlsm',
    ),
    77 =>
    array(
        'id' => 176,
        'iso3' => 'PYF',
        'name' => 'French Polynesia',
        'path' => 'data/cache/country_data/Polynesia_french_12.xlsm',
    ),
    78 =>
    array(
        'id' => 217,
        'iso3' => 'ATF',
        'name' => 'French Southern Territories',
        'path' => '',
    ),
    79 =>
    array(
        'id' => 76,
        'iso3' => 'GAB',
        'name' => 'Gabon',
        'path' => 'data/cache/country_data/Gabon_12.xlsm',
    ),
    80 =>
    array(
        'id' => 85,
        'iso3' => 'GMB',
        'name' => 'Gambia',
        'path' => 'data/cache/country_data/Gambia_12.xlsm',
    ),
    81 =>
    array(
        'id' => 79,
        'iso3' => 'GEO',
        'name' => 'Georgia',
        'path' => 'data/cache/country_data/Georgia_12.xlsm',
    ),
    82 =>
    array(
        'id' => 57,
        'iso3' => 'DEU',
        'name' => 'Germany',
        'path' => 'data/cache/country_data/Germany_12.xlsm',
    ),
    83 =>
    array(
        'id' => 82,
        'iso3' => 'GHA',
        'name' => 'Ghana',
        'path' => 'data/cache/country_data/Ghana_12.xlsm',
    ),
    84 =>
    array(
        'id' => 83,
        'iso3' => 'GIB',
        'name' => 'Gibraltar',
        'path' => 'data/cache/country_data/Gibraltar_12.xlsm',
    ),
    85 =>
    array(
        'id' => 89,
        'iso3' => 'GRC',
        'name' => 'Greece',
        'path' => 'data/cache/country_data/Greece_12.xlsm',
    ),
    86 =>
    array(
        'id' => 84,
        'iso3' => 'GRL',
        'name' => 'Greenland',
        'path' => 'data/cache/country_data/Greenland_12.xlsm',
    ),
    87 =>
    array(
        'id' => 78,
        'iso3' => 'GRD',
        'name' => 'Grenada',
        'path' => 'data/cache/country_data/Grenada_12.xlsm',
    ),
    88 =>
    array(
        'id' => 87,
        'iso3' => 'GLP',
        'name' => 'Guadeloupe',
        'path' => 'data/cache/country_data/guadeloupe_12.xlsm',
    ),
    89 =>
    array(
        'id' => 92,
        'iso3' => 'GUM',
        'name' => 'Guam',
        'path' => 'data/cache/country_data/Guam_12.xlsm',
    ),
    90 =>
    array(
        'id' => 91,
        'iso3' => 'GTM',
        'name' => 'Guatemala',
        'path' => 'data/cache/country_data/guatemala_12.xlsm',
    ),
    91 =>
    array(
        'id' => 253,
        'iso3' => 'CIS',
        'name' => 'Channel Islands',
        'path' => 'data/cache/country_data/Channel_islands_12.xlsm',
    ),
    92 =>
    array(
        'id' => 86,
        'iso3' => 'GIN',
        'name' => 'Guinea',
        'path' => 'data/cache/country_data/Guinea_12.xlsm',
    ),
    93 =>
    array(
        'id' => 93,
        'iso3' => 'GNB',
        'name' => 'Guinea-Bissau',
        'path' => 'data/cache/country_data/Guinea_Bissau_12.xlsm',
    ),
    94 =>
    array(
        'id' => 94,
        'iso3' => 'GUY',
        'name' => 'Guyana',
        'path' => 'data/cache/country_data/guyana_12.xlsm',
    ),
    95 =>
    array(
        'id' => 99,
        'iso3' => 'HTI',
        'name' => 'Haiti',
        'path' => 'data/cache/country_data/haiti_12.xlsm',
    ),
    96 =>
    array(
        'id' => 96,
        'iso3' => 'HMD',
        'name' => 'Heard Island and McDonald Islands',
        'path' => '',
    ),
    97 =>
    array(
        'id' => 97,
        'iso3' => 'HND',
        'name' => 'Honduras',
        'path' => 'data/cache/country_data/honduras_12.xlsm',
    ),
    98 =>
    array(
        'id' => 95,
        'iso3' => 'HKG',
        'name' => 'Hong Kong',
        'path' => 'data/cache/country_data/China_hong_kong_12.xlsm',
    ),
    99 =>
    array(
        'id' => 100,
        'iso3' => 'HUN',
        'name' => 'Hungary',
        'path' => 'data/cache/country_data/Hungary_12.xlsm',
    ),
    100 =>
    array(
        'id' => 109,
        'iso3' => 'ISL',
        'name' => 'Iceland',
        'path' => 'data/cache/country_data/Iceland_12.xlsm',
    ),
    101 =>
    array(
        'id' => 105,
        'iso3' => 'IND',
        'name' => 'India',
        'path' => 'data/cache/country_data/India_12.xlsm',
    ),
    102 =>
    array(
        'id' => 101,
        'iso3' => 'IDN',
        'name' => 'Indonesia',
        'path' => 'data/cache/country_data/Indonesia_12.xlsm',
    ),
    103 =>
    array(
        'id' => 108,
        'iso3' => 'IRN',
        'name' => 'Iran',
        'path' => 'data/cache/country_data/Iran_islamic_rep_of_12.xlsm',
    ),
    104 =>
    array(
        'id' => 107,
        'iso3' => 'IRQ',
        'name' => 'Iraq',
        'path' => 'data/cache/country_data/Iraq_12.xlsm',
    ),
    105 =>
    array(
        'id' => 102,
        'iso3' => 'IRL',
        'name' => 'Ireland',
        'path' => 'data/cache/country_data/Ireland_12.xlsm',
    ),
    106 =>
    array(
        'id' => 104,
        'iso3' => 'IMN',
        'name' => 'Isle of Man',
        'path' => 'data/cache/country_data/Man_isle_of_12.xlsm',
    ),
    107 =>
    array(
        'id' => 103,
        'iso3' => 'ISR',
        'name' => 'Israel',
        'path' => 'data/cache/country_data/Israel_12.xlsm',
    ),
    108 =>
    array(
        'id' => 110,
        'iso3' => 'ITA',
        'name' => 'Italy',
        'path' => 'data/cache/country_data/Italy_12.xlsm',
    ),
    109 =>
    array(
        'id' => 44,
        'iso3' => 'CIV',
        'name' => 'Ivory Coast',
        'path' => 'data/cache/country_data/Cote_d_Ivoire_12.xlsm',
    ),
    110 =>
    array(
        'id' => 112,
        'iso3' => 'JAM',
        'name' => 'Jamaica',
        'path' => 'data/cache/country_data/jamaica_12.xlsm',
    ),
    111 =>
    array(
        'id' => 114,
        'iso3' => 'JPN',
        'name' => 'Japan',
        'path' => 'data/cache/country_data/Japan_12.xlsm',
    ),
    112 =>
    array(
        'id' => 111,
        'iso3' => 'JEY',
        'name' => 'Jersey',
        'path' => '',
    ),
    113 =>
    array(
        'id' => 113,
        'iso3' => 'JOR',
        'name' => 'Jordan',
        'path' => 'data/cache/country_data/Jordan_12.xlsm',
    ),
    114 =>
    array(
        'id' => 126,
        'iso3' => 'KAZ',
        'name' => 'Kazakhstan',
        'path' => 'data/cache/country_data/Kazakhstan_12.xlsm',
    ),
    115 =>
    array(
        'id' => 115,
        'iso3' => 'KEN',
        'name' => 'Kenya',
        'path' => 'data/cache/country_data/Kenya_12.xlsm',
    ),
    116 =>
    array(
        'id' => 118,
        'iso3' => 'KIR',
        'name' => 'Kiribati',
        'path' => 'data/cache/country_data/Kiribati_12.xlsm',
    ),
    117 =>
    array(
        'id' => 123,
        'iso3' => 'XKX',
        'name' => 'Kosovo',
        'path' => '',
    ),
    118 =>
    array(
        'id' => 124,
        'iso3' => 'KWT',
        'name' => 'Kuwait',
        'path' => 'data/cache/country_data/Kuwait_12.xlsm',
    ),
    119 =>
    array(
        'id' => 116,
        'iso3' => 'KGZ',
        'name' => 'Kyrgyzstan',
        'path' => 'data/cache/country_data/Kyrgyzstan_12.xlsm',
    ),
    120 =>
    array(
        'id' => 127,
        'iso3' => 'LAO',
        'name' => 'Laos',
        'path' => 'data/cache/country_data/Lao_people_dem_rep_12.xlsm',
    ),
    121 =>
    array(
        'id' => 136,
        'iso3' => 'LVA',
        'name' => 'Latvia',
        'path' => 'data/cache/country_data/Latvia_12.xlsm',
    ),
    122 =>
    array(
        'id' => 128,
        'iso3' => 'LBN',
        'name' => 'Lebanon',
        'path' => 'data/cache/country_data/Lebanon_12.xlsm',
    ),
    123 =>
    array(
        'id' => 133,
        'iso3' => 'LSO',
        'name' => 'Lesotho',
        'path' => 'data/cache/country_data/Lesotho_12.xlsm',
    ),
    124 =>
    array(
        'id' => 132,
        'iso3' => 'LBR',
        'name' => 'Liberia',
        'path' => 'data/cache/country_data/Liberia_12.xlsm',
    ),
    125 =>
    array(
        'id' => 137,
        'iso3' => 'LBY',
        'name' => 'Libya',
        'path' => 'data/cache/country_data/Libyan_arab_jamahiriya_12.xlsm',
    ),
    126 =>
    array(
        'id' => 130,
        'iso3' => 'LIE',
        'name' => 'Liechtenstein',
        'path' => 'data/cache/country_data/Liechtenstein_12.xlsm',
    ),
    127 =>
    array(
        'id' => 134,
        'iso3' => 'LTU',
        'name' => 'Lithuania',
        'path' => 'data/cache/country_data/Lithuania_12.xlsm',
    ),
    128 =>
    array(
        'id' => 135,
        'iso3' => 'LUX',
        'name' => 'Luxembourg',
        'path' => 'data/cache/country_data/Luxembourg_12.xlsm',
    ),
    129 =>
    array(
        'id' => 149,
        'iso3' => 'MAC',
        'name' => 'Macao',
        'path' => 'data/cache/country_data/Macau_12.xlsm',
    ),
    130 =>
    array(
        'id' => 145,
        'iso3' => 'MKD',
        'name' => 'Macedonia',
        'path' => 'data/cache/country_data/Macedonia_TFYR_12.xlsm',
    ),
    131 =>
    array(
        'id' => 143,
        'iso3' => 'MDG',
        'name' => 'Madagascar',
        'path' => 'data/cache/country_data/Madagascar_12.xlsm',
    ),
    132 =>
    array(
        'id' => 157,
        'iso3' => 'MWI',
        'name' => 'Malawi',
        'path' => 'data/cache/country_data/Malawi_12.xlsm',
    ),
    133 =>
    array(
        'id' => 159,
        'iso3' => 'MYS',
        'name' => 'Malaysia',
        'path' => 'data/cache/country_data/Malaysia_12.xlsm',
    ),
    134 =>
    array(
        'id' => 156,
        'iso3' => 'MDV',
        'name' => 'Maldives',
        'path' => 'data/cache/country_data/Maldives_12.xlsm',
    ),
    135 =>
    array(
        'id' => 146,
        'iso3' => 'MLI',
        'name' => 'Mali',
        'path' => 'data/cache/country_data/Mali_12.xlsm',
    ),
    136 =>
    array(
        'id' => 154,
        'iso3' => 'MLT',
        'name' => 'Malta',
        'path' => 'data/cache/country_data/Malta_12.xlsm',
    ),
    137 =>
    array(
        'id' => 144,
        'iso3' => 'MHL',
        'name' => 'Marshall Islands',
        'path' => 'data/cache/country_data/Marshall_islands_12.xlsm',
    ),
    138 =>
    array(
        'id' => 151,
        'iso3' => 'MTQ',
        'name' => 'Martinique',
        'path' => 'data/cache/country_data/martinique_12.xlsm',
    ),
    139 =>
    array(
        'id' => 152,
        'iso3' => 'MRT',
        'name' => 'Mauritania',
        'path' => 'data/cache/country_data/Mauritania_12.xlsm',
    ),
    140 =>
    array(
        'id' => 155,
        'iso3' => 'MUS',
        'name' => 'Mauritius',
        'path' => 'data/cache/country_data/Mauritius_12.xlsm',
    ),
    141 =>
    array(
        'id' => 247,
        'iso3' => 'MYT',
        'name' => 'Mayotte',
        'path' => 'data/cache/country_data/Mayotte_12.xlsm',
    ),
    142 =>
    array(
        'id' => 158,
        'iso3' => 'MEX',
        'name' => 'Mexico',
        'path' => 'data/cache/country_data/mexico_12.xlsm',
    ),
    143 =>
    array(
        'id' => 73,
        'iso3' => 'FSM',
        'name' => 'Micronesia',
        'path' => 'data/cache/country_data/Micronesia_fed_states_of_12.xlsm',
    ),
    144 =>
    array(
        'id' => 140,
        'iso3' => 'MDA',
        'name' => 'Moldova',
        'path' => 'data/cache/country_data/Moldova_rep_of_12.xlsm',
    ),
    145 =>
    array(
        'id' => 139,
        'iso3' => 'MCO',
        'name' => 'Monaco',
        'path' => 'data/cache/country_data/Monaco_12.xlsm',
    ),
    146 =>
    array(
        'id' => 148,
        'iso3' => 'MNG',
        'name' => 'Mongolia',
        'path' => 'data/cache/country_data/Mongolia_12.xlsm',
    ),
    147 =>
    array(
        'id' => 141,
        'iso3' => 'MNE',
        'name' => 'Montenegro',
        'path' => 'data/cache/country_data/Montenegro_12.xlsm',
    ),
    148 =>
    array(
        'id' => 153,
        'iso3' => 'MSR',
        'name' => 'Montserrat',
        'path' => 'data/cache/country_data/Montserrat_12.xlsm',
    ),
    149 =>
    array(
        'id' => 138,
        'iso3' => 'MAR',
        'name' => 'Morocco',
        'path' => 'data/cache/country_data/Morocco_12.xlsm',
    ),
    150 =>
    array(
        'id' => 160,
        'iso3' => 'MOZ',
        'name' => 'Mozambique',
        'path' => 'data/cache/country_data/Mozambique_12.xlsm',
    ),
    151 =>
    array(
        'id' => 147,
        'iso3' => 'MMR',
        'name' => 'Myanmar',
        'path' => 'data/cache/country_data/Myanmar_12.xlsm',
    ),
    152 =>
    array(
        'id' => 161,
        'iso3' => 'NAM',
        'name' => 'Namibia',
        'path' => 'data/cache/country_data/Namibia_12.xlsm',
    ),
    153 =>
    array(
        'id' => 170,
        'iso3' => 'NRU',
        'name' => 'Nauru',
        'path' => 'data/cache/country_data/Nauru_12.xlsm',
    ),
    154 =>
    array(
        'id' => 169,
        'iso3' => 'NPL',
        'name' => 'Nepal',
        'path' => 'data/cache/country_data/Nepal_12.xlsm',
    ),
    155 =>
    array(
        'id' => 167,
        'iso3' => 'NLD',
        'name' => 'Netherlands',
        'path' => 'data/cache/country_data/Netherlands_12.xlsm',
    ),
    156 =>
    array(
        'id' => 252,
        'iso3' => 'ANT',
        'name' => 'Netherlands Antilles',
        'path' => 'data/cache/country_data/netherlands_antilles_12.xlsm',
    ),
    157 =>
    array(
        'id' => 162,
        'iso3' => 'NCL',
        'name' => 'New Caledonia',
        'path' => 'data/cache/country_data/New_caledonia_12.xlsm',
    ),
    158 =>
    array(
        'id' => 172,
        'iso3' => 'NZL',
        'name' => 'New Zealand',
        'path' => 'data/cache/country_data/New_Zealand_12.xlsm',
    ),
    159 =>
    array(
        'id' => 166,
        'iso3' => 'NIC',
        'name' => 'Nicaragua',
        'path' => 'data/cache/country_data/nicaragua_12.xlsm',
    ),
    160 =>
    array(
        'id' => 163,
        'iso3' => 'NER',
        'name' => 'Niger',
        'path' => 'data/cache/country_data/Niger_12.xlsm',
    ),
    161 =>
    array(
        'id' => 165,
        'iso3' => 'NGA',
        'name' => 'Nigeria',
        'path' => 'data/cache/country_data/Nigeria_12.xlsm',
    ),
    162 =>
    array(
        'id' => 171,
        'iso3' => 'NIU',
        'name' => 'Niue',
        'path' => 'data/cache/country_data/Niue_12.xlsm',
    ),
    163 =>
    array(
        'id' => 164,
        'iso3' => 'NFK',
        'name' => 'Norfolk Island',
        'path' => '',
    ),
    164 =>
    array(
        'id' => 150,
        'iso3' => 'MNP',
        'name' => 'Northern Mariana Islands',
        'path' => 'data/cache/country_data/Mariana_islands_northern_12.xlsm',
    ),
    165 =>
    array(
        'id' => 121,
        'iso3' => 'PRK',
        'name' => 'North Korea',
        'path' => 'data/cache/country_data/Korea_dem_peoples_rep_of_12.xlsm',
    ),
    166 =>
    array(
        'id' => 168,
        'iso3' => 'NOR',
        'name' => 'Norway',
        'path' => 'data/cache/country_data/Norway_12.xlsm',
    ),
    167 =>
    array(
        'id' => 173,
        'iso3' => 'OMN',
        'name' => 'Oman',
        'path' => 'data/cache/country_data/Oman_12.xlsm',
    ),
    168 =>
    array(
        'id' => 179,
        'iso3' => 'PAK',
        'name' => 'Pakistan',
        'path' => 'data/cache/country_data/Pakistan_12.xlsm',
    ),
    169 =>
    array(
        'id' => 186,
        'iso3' => 'PLW',
        'name' => 'Palau',
        'path' => 'data/cache/country_data/Palau_12.xlsm',
    ),
    170 =>
    array(
        'id' => 184,
        'iso3' => 'PSE',
        'name' => 'Palestinian Territory',
        'path' => 'data/cache/country_data/Palestine_12.xlsm',
    ),
    171 =>
    array(
        'id' => 174,
        'iso3' => 'PAN',
        'name' => 'Panama',
        'path' => 'data/cache/country_data/panama_12.xlsm',
    ),
    172 =>
    array(
        'id' => 177,
        'iso3' => 'PNG',
        'name' => 'Papua New Guinea',
        'path' => 'data/cache/country_data/Papua_new_guinea_12.xlsm',
    ),
    173 =>
    array(
        'id' => 187,
        'iso3' => 'PRY',
        'name' => 'Paraguay',
        'path' => 'data/cache/country_data/paraguay_12.xlsm',
    ),
    174 =>
    array(
        'id' => 175,
        'iso3' => 'PER',
        'name' => 'Peru',
        'path' => 'data/cache/country_data/peru_12.xlsm',
    ),
    175 =>
    array(
        'id' => 178,
        'iso3' => 'PHL',
        'name' => 'Philippines',
        'path' => 'data/cache/country_data/Philippines_12.xlsm',
    ),
    176 =>
    array(
        'id' => 182,
        'iso3' => 'PCN',
        'name' => 'Pitcairn',
        'path' => '',
    ),
    177 =>
    array(
        'id' => 180,
        'iso3' => 'POL',
        'name' => 'Poland',
        'path' => 'data/cache/country_data/Poland_12.xlsm',
    ),
    178 =>
    array(
        'id' => 185,
        'iso3' => 'PRT',
        'name' => 'Portugal',
        'path' => 'data/cache/country_data/Portugal_12.xlsm',
    ),
    179 =>
    array(
        'id' => 183,
        'iso3' => 'PRI',
        'name' => 'Puerto Rico',
        'path' => 'data/cache/country_data/puerto_rico_12.xlsm',
    ),
    180 =>
    array(
        'id' => 188,
        'iso3' => 'QAT',
        'name' => 'Qatar',
        'path' => 'data/cache/country_data/Qatar_12.xlsm',
    ),
    181 =>
    array(
        'id' => 42,
        'iso3' => 'COG',
        'name' => 'Republic of the Congo',
        'path' => 'data/cache/country_data/Congo_12.xlsm',
    ),
    182 =>
    array(
        'id' => 189,
        'iso3' => 'REU',
        'name' => 'Reunion',
        'path' => 'data/cache/country_data/Reunion_12.xlsm',
    ),
    183 =>
    array(
        'id' => 190,
        'iso3' => 'ROU',
        'name' => 'Romania',
        'path' => 'data/cache/country_data/Romania_12.xlsm',
    ),
    184 =>
    array(
        'id' => 192,
        'iso3' => 'RUS',
        'name' => 'Russia',
        'path' => 'data/cache/country_data/Russian_fed_12.xlsm',
    ),
    185 =>
    array(
        'id' => 193,
        'iso3' => 'RWA',
        'name' => 'Rwanda',
        'path' => 'data/cache/country_data/Rwanda_12.xlsm',
    ),
    186 =>
    array(
        'id' => 26,
        'iso3' => 'BLM',
        'name' => 'Saint Barthelemy',
        'path' => '',
    ),
    187 =>
    array(
        'id' => 201,
        'iso3' => 'SHN',
        'name' => 'Saint Helena',
        'path' => 'data/cache/country_data/Saint_Helena_12.xlsm',
    ),
    188 =>
    array(
        'id' => 120,
        'iso3' => 'KNA',
        'name' => 'Saint Kitts and Nevis',
        'path' => 'data/cache/country_data/saint_kitts_and_nevis_12.xlsm',
    ),
    189 =>
    array(
        'id' => 129,
        'iso3' => 'LCA',
        'name' => 'Saint Lucia',
        'path' => 'data/cache/country_data/saint_lucia_12.xlsm',
    ),
    190 =>
    array(
        'id' => 142,
        'iso3' => 'MAF',
        'name' => 'Saint Martin',
        'path' => '',
    ),
    191 =>
    array(
        'id' => 181,
        'iso3' => 'SPM',
        'name' => 'Saint Pierre and Miquelon',
        'path' => 'data/cache/country_data/St_Pierre_and_Miquelon_12.xlsm',
    ),
    192 =>
    array(
        'id' => 238,
        'iso3' => 'VCT',
        'name' => 'Saint Vincent and the Grenadines',
        'path' => 'data/cache/country_data/st_vincent_and_grenad_12.xlsm',
    ),
    193 =>
    array(
        'id' => 245,
        'iso3' => 'WSM',
        'name' => 'Samoa',
        'path' => 'data/cache/country_data/Samoa_12.xlsm',
    ),
    194 =>
    array(
        'id' => 206,
        'iso3' => 'SMR',
        'name' => 'San Marino',
        'path' => 'data/cache/country_data/San_marino_12.xlsm',
    ),
    195 =>
    array(
        'id' => 210,
        'iso3' => 'STP',
        'name' => 'Sao Tome and Principe',
        'path' => 'data/cache/country_data/Sao_tome_and_principe_12.xlsm',
    ),
    196 =>
    array(
        'id' => 194,
        'iso3' => 'SAU',
        'name' => 'Saudi Arabia',
        'path' => 'data/cache/country_data/Saudi_arabia_12.xlsm',
    ),
    197 =>
    array(
        'id' => 207,
        'iso3' => 'SEN',
        'name' => 'Senegal',
        'path' => 'data/cache/country_data/Senegal_12.xlsm',
    ),
    198 =>
    array(
        'id' => 191,
        'iso3' => 'SRB',
        'name' => 'Serbia',
        'path' => 'data/cache/country_data/Serbia_12.xlsm',
    ),
    199 =>
    array(
        'id' => 196,
        'iso3' => 'SYC',
        'name' => 'Seychelles',
        'path' => 'data/cache/country_data/Seychelles_12.xlsm',
    ),
    200 =>
    array(
        'id' => 205,
        'iso3' => 'SLE',
        'name' => 'Sierra Leone',
        'path' => 'data/cache/country_data/Sierra_Leone_12.xlsm',
    ),
    201 =>
    array(
        'id' => 200,
        'iso3' => 'SGP',
        'name' => 'Singapore',
        'path' => 'data/cache/country_data/Singapore_12.xlsm',
    ),
    202 =>
    array(
        'id' => 212,
        'iso3' => 'SXM',
        'name' => 'Sint Maarten',
        'path' => '',
    ),
    203 =>
    array(
        'id' => 204,
        'iso3' => 'SVK',
        'name' => 'Slovakia',
        'path' => 'data/cache/country_data/Slovakia_12.xlsm',
    ),
    204 =>
    array(
        'id' => 202,
        'iso3' => 'SVN',
        'name' => 'Slovenia',
        'path' => 'data/cache/country_data/Slovenia_12.xlsm',
    ),
    205 =>
    array(
        'id' => 195,
        'iso3' => 'SLB',
        'name' => 'Solomon Islands',
        'path' => 'data/cache/country_data/Solomon_islands_12.xlsm',
    ),
    206 =>
    array(
        'id' => 208,
        'iso3' => 'SOM',
        'name' => 'Somalia',
        'path' => 'data/cache/country_data/Somalia_12.xlsm',
    ),
    207 =>
    array(
        'id' => 248,
        'iso3' => 'ZAF',
        'name' => 'South Africa',
        'path' => 'data/cache/country_data/South_Africa_12.xlsm',
    ),
    208 =>
    array(
        'id' => 90,
        'iso3' => 'SGS',
        'name' => 'South Georgia and the South Sandwich Islands',
        'path' => '',
    ),
    209 =>
    array(
        'id' => 122,
        'iso3' => 'KOR',
        'name' => 'South Korea',
        'path' => 'data/cache/country_data/Korea_rep_of_12.xlsm',
    ),
    210 =>
    array(
        'id' => 198,
        'iso3' => 'SSD',
        'name' => 'South Sudan',
        'path' => 'data/cache/country_data/South_Sudan_12.xlsm',
    ),
    211 =>
    array(
        'id' => 68,
        'iso3' => 'ESP',
        'name' => 'Spain',
        'path' => 'data/cache/country_data/Spain_12.xlsm',
    ),
    212 =>
    array(
        'id' => 131,
        'iso3' => 'LKA',
        'name' => 'Sri Lanka',
        'path' => 'data/cache/country_data/Sri_lanka_12.xlsm',
    ),
    213 =>
    array(
        'id' => 197,
        'iso3' => 'SDN',
        'name' => 'Sudan',
        'path' => 'data/cache/country_data/Sudan_12.xlsm',
    ),
    214 =>
    array(
        'id' => 209,
        'iso3' => 'SUR',
        'name' => 'Suriname',
        'path' => 'data/cache/country_data/suriname_12.xlsm',
    ),
    215 =>
    array(
        'id' => 203,
        'iso3' => 'SJM',
        'name' => 'Svalbard and Jan Mayen',
        'path' => '',
    ),
    216 =>
    array(
        'id' => 214,
        'iso3' => 'SWZ',
        'name' => 'Swaziland',
        'path' => 'data/cache/country_data/Swaziland_12.xlsm',
    ),
    217 =>
    array(
        'id' => 199,
        'iso3' => 'SWE',
        'name' => 'Sweden',
        'path' => 'data/cache/country_data/Sweden_12.xlsm',
    ),
    218 =>
    array(
        'id' => 43,
        'iso3' => 'CHE',
        'name' => 'Switzerland',
        'path' => 'data/cache/country_data/Switzerland_12.xlsm',
    ),
    219 =>
    array(
        'id' => 213,
        'iso3' => 'SYR',
        'name' => 'Syria',
        'path' => 'data/cache/country_data/Syrian_arab_rep_12.xlsm',
    ),
    220 =>
    array(
        'id' => 229,
        'iso3' => 'TWN',
        'name' => 'Taiwan',
        'path' => '',
    ),
    221 =>
    array(
        'id' => 220,
        'iso3' => 'TJK',
        'name' => 'Tajikistan',
        'path' => 'data/cache/country_data/Tajikistan_12.xlsm',
    ),
    222 =>
    array(
        'id' => 230,
        'iso3' => 'TZA',
        'name' => 'Tanzania',
        'path' => 'data/cache/country_data/Tanzania_united_rep_of_12.xlsm',
    ),
    223 =>
    array(
        'id' => 219,
        'iso3' => 'THA',
        'name' => 'Thailand',
        'path' => 'data/cache/country_data/Thailand_12.xlsm',
    ),
    224 =>
    array(
        'id' => 218,
        'iso3' => 'TGO',
        'name' => 'Togo',
        'path' => 'data/cache/country_data/Togo_12.xlsm',
    ),
    225 =>
    array(
        'id' => 221,
        'iso3' => 'TKL',
        'name' => 'Tokelau',
        'path' => 'data/cache/country_data/Tokelau_12.xlsm',
    ),
    226 =>
    array(
        'id' => 225,
        'iso3' => 'TON',
        'name' => 'Tonga',
        'path' => 'data/cache/country_data/Tonga_12.xlsm',
    ),
    227 =>
    array(
        'id' => 227,
        'iso3' => 'TTO',
        'name' => 'Trinidad and Tobago',
        'path' => 'data/cache/country_data/Trinidad_and_Tobago_12.xlsm',
    ),
    228 =>
    array(
        'id' => 224,
        'iso3' => 'TUN',
        'name' => 'Tunisia',
        'path' => 'data/cache/country_data/Tunisia_12.xlsm',
    ),
    229 =>
    array(
        'id' => 226,
        'iso3' => 'TUR',
        'name' => 'Turkey',
        'path' => 'data/cache/country_data/Turkey_12.xlsm',
    ),
    230 =>
    array(
        'id' => 223,
        'iso3' => 'TKM',
        'name' => 'Turkmenistan',
        'path' => 'data/cache/country_data/Turkmenistan_12.xlsm',
    ),
    231 =>
    array(
        'id' => 215,
        'iso3' => 'TCA',
        'name' => 'Turks and Caicos Islands',
        'path' => 'data/cache/country_data/Turks_and_Caicos_Islands_12.xlsm',
    ),
    232 =>
    array(
        'id' => 228,
        'iso3' => 'TUV',
        'name' => 'Tuvalu',
        'path' => 'data/cache/country_data/Tuvalu_12.xlsm',
    ),
    233 =>
    array(
        'id' => 232,
        'iso3' => 'UGA',
        'name' => 'Uganda',
        'path' => 'data/cache/country_data/Uganda_12.xlsm',
    ),
    234 =>
    array(
        'id' => 231,
        'iso3' => 'UKR',
        'name' => 'Ukraine',
        'path' => 'data/cache/country_data/Ukraine_12.xlsm',
    ),
    235 =>
    array(
        'id' => 2,
        'iso3' => 'ARE',
        'name' => 'United Arab Emirates',
        'path' => 'data/cache/country_data/Arab_emirates_united_12.xlsm',
    ),
    236 =>
    array(
        'id' => 77,
        'iso3' => 'GBR',
        'name' => 'United Kingdom',
        'path' => 'data/cache/country_data/United_kingdom_12.xlsm',
    ),
    237 =>
    array(
        'id' => 234,
        'iso3' => 'USA',
        'name' => 'United States',
        'path' => 'data/cache/country_data/America_united_states_of_12.xlsm',
    ),
    238 =>
    array(
        'id' => 233,
        'iso3' => 'UMI',
        'name' => 'United States Minor Outlying Islands',
        'path' => '',
    ),
    239 =>
    array(
        'id' => 235,
        'iso3' => 'URY',
        'name' => 'Uruguay',
        'path' => 'data/cache/country_data/uruguay_12.xlsm',
    ),
    240 =>
    array(
        'id' => 241,
        'iso3' => 'VIR',
        'name' => 'U.S. Virgin Islands',
        'path' => 'data/cache/country_data/united_states_virgin_islands_12.xlsm',
    ),
    241 =>
    array(
        'id' => 236,
        'iso3' => 'UZB',
        'name' => 'Uzbekistan',
        'path' => 'data/cache/country_data/Uzbekistan_12.xlsm',
    ),
    242 =>
    array(
        'id' => 243,
        'iso3' => 'VUT',
        'name' => 'Vanuatu',
        'path' => 'data/cache/country_data/Vanuatu_12.xlsm',
    ),
    243 =>
    array(
        'id' => 237,
        'iso3' => 'VAT',
        'name' => 'Vatican',
        'path' => 'data/cache/country_data/Holy_see_12.xlsm',
    ),
    244 =>
    array(
        'id' => 239,
        'iso3' => 'VEN',
        'name' => 'Venezuela',
        'path' => 'data/cache/country_data/venezuela_12.xlsm',
    ),
    245 =>
    array(
        'id' => 242,
        'iso3' => 'VNM',
        'name' => 'Vietnam',
        'path' => 'data/cache/country_data/Viet_Nam_12.xlsm',
    ),
    246 =>
    array(
        'id' => 244,
        'iso3' => 'WLF',
        'name' => 'Wallis and Futuna',
        'path' => '',
    ),
    247 =>
    array(
        'id' => 66,
        'iso3' => 'ESH',
        'name' => 'Western Sahara',
        'path' => 'data/cache/country_data/Sahara_western_12.xlsm',
    ),
    248 =>
    array(
        'id' => 246,
        'iso3' => 'YEM',
        'name' => 'Yemen',
        'path' => 'data/cache/country_data/Yemen_12.xlsm',
    ),
    249 =>
    array(
        'id' => 249,
        'iso3' => 'ZMB',
        'name' => 'Zambia',
        'path' => 'data/cache/country_data/Zambia_12.xlsm',
    ),
    250 =>
    array(
        'id' => 250,
        'iso3' => 'ZWE',
        'name' => 'Zimbabwe',
        'path' => 'data/cache/country_data/Zimbabwe_12.xlsm',
    ),
);
