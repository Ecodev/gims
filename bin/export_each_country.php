<?php

/**
 * This script will import individually each country into DB and then call GIMS
 * API to export computed results
 */
$countries = require (__DIR__ . '/countries.php');

/**
 * Export computed results for each country files
 * @param array $countries
 * @param array $onlyThose
 */
function export(array $countries, array $onlyThose = array())
{
    $hostname = basename(getcwd());
    @mkdir('each');
    @mkdir('each/backup');
    @mkdir('each/questionnaire');
    @mkdir('each/country');

    foreach ($countries as $country) {

        $id = $country['id'];
        $name = $country['name'];
        $path = $country['path'];

        if ($onlyThose && array_search($name, $onlyThose) === false) {
            continue;
        }

        if (!$path) {
            continue;
        }

        echo $path . PHP_EOL;
        $backup = "each/backup/$name.backup.gz";

        // Recycle backup if exists
        if (is_readable($backup)) {
            echo `phing load-data -DdumpFile="$backup"`;
        } else {
            echo `phing load-data -DdumpFile=../gims/population.backup.gz`;
            echo `php htdocs/index.php import jmp $path`;
            echo `phing dump-data -DdumpFile="$backup"`;
        }

        echo `wget -O "each/questionnaire/$name - Water.csv"      "http://$hostname.local/api/table/questionnaire/foo.csv?years=1990-2011&country=$id&filterSet=2"`;
        echo `wget -O "each/questionnaire/$name - Sanitation.csv" "http://$hostname.local/api/table/questionnaire/foo.csv?years=1990-2011&country=$id&filterSet=5"`;

//        echo `wget -O "each/country/$name - Water.csv"      "http://$hostname.local/api/table/country/foo.csv?years=1990-2011&country=$id&filterSet=2"`;
//        echo `wget -O "each/country/$name - Sanitation.csv" "http://$hostname.local/api/table/country/foo.csv?years=1990-2011&country=$id&filterSet=5"`;
    }
}

$onlyThose = array(
//    'Zambia',
//    'Swaziland',
//    'Pakistan',
//    'Namibia',
//    'Lithuania',
//    'Brazil',
//    'Belgium',
);

export($countries, $onlyThose);
