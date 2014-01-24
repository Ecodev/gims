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
    @mkdir('actual');
    @mkdir('actual/backup');
    @mkdir('actual/questionnaire');
    @mkdir('actual/country');

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
        $backup = "actual/backup/$name.backup.gz";

        // Recycle backup if exists
        if (is_readable($backup)) {
            echo `phing load-data -DdumpFile="$backup"`;
        } else {
            echo `phing load-data -DdumpFile=../gims/population.backup.gz`;


            $returnStatus = null;
            passthru("php htdocs/index.php import jmp $path", $returnStatus);
            if ($returnStatus) {
                echo "FATAL: could not import '$path', abort export for this country" . PHP_EOL;
                continue;
            }
            echo `phing dump-data -DdumpFile="$backup"`;
        }

        echo `wget -O "actual/questionnaire/$name - Water.csv"      "http://$hostname.local/api/table/questionnaire/foo.csv?country=$id&filterSet=2"`;
        echo `wget -O "actual/questionnaire/$name - Sanitation.csv" "http://$hostname.local/api/table/questionnaire/foo.csv?country=$id&filterSet=5"`;

        echo `wget -O "actual/country/$name - Water.csv"      "http://$hostname.local/api/table/country/foo.csv?years=1980-2012&country=$id&filterSet=2"`;
        echo `wget -O "actual/country/$name - Sanitation.csv" "http://$hostname.local/api/table/country/foo.csv?years=1980-2012&country=$id&filterSet=5"`;
    }
}

$onlyThose = $argv;
array_shift($onlyThose);

export($countries, $onlyThose);
