<?php

/**
 * This script will import individually each country into DB and then call GIMS
 * API to export computed results
 */
$countries = require __DIR__ . '/countries.php';

/**
 * Export computed results for each country files
 * @param array $countries
 * @param array $onlyThose
 */
function export(array $countries, array $onlyThose = [])
{
    $hostname = basename(getcwd());
    @mkdir('actual');
//    @mkdir('actual/questionnaire');
    @mkdir('actual/country');

    foreach ($countries as $country) {
        $id = $country['id'];
        $name = $country['name'];
        $path = $country['path'];

        if ($onlyThose && array_search($name, $onlyThose) === false) {
            continue;
        }

        if (!$path || $path == 'region') {
            continue;
        }

        echo $path . PHP_EOL;

//        echo `wget -O "actual/questionnaire/$name - Water.csv"      "http://$hostname/api/table/questionnaire/foo.csv?country=$id&filterSet=2"`;
//        echo `wget -O "actual/questionnaire/$name - Sanitation.csv" "http://$hostname/api/table/questionnaire/foo.csv?country=$id&filterSet=5"`;

        echo `wget -O "actual/country/$name - Water.csv"      "http://$hostname/api/table/country/foo.csv?filters=261,262,263,264,265&geonames=$id&years=1980-2015"`;
        echo `wget -O "actual/country/$name - Sanitation.csv" "http://$hostname/api/table/country/foo.csv?filters=352,353,354,355,356,357&geonames=$id&years=1980-2015"`;
    }
}

$onlyThose = $argv;
array_shift($onlyThose);

export($countries, $onlyThose);
