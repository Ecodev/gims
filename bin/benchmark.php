<?php

/**
 * This will return an
 */
function benchmark()
{
    $hostname = basename(getcwd());

    $urls = array(
//        "http://$hostname.lan/api/table/filter?questionnaire=1&filterSet=2",
//        "http://$hostname.lan/api/table/filter?questionnaire=1&filterSet=5",
//        "http://$hostname.lan/api/table/filter?questionnaire=1,2,3,4&filterSet=2",
//        "http://$hostname.lan/api/table/filter?questionnaire=1,2,3,4&filterSet=5",
//        "http://$hostname.lan/api/table/questionnaire?country=19&filterSet=2",
//        "http://$hostname.lan/api/table/questionnaire?country=19&filterSet=5",
//        "http://$hostname.lan/api/table/questionnaire?country=19,3,57&filterSet=2",
//        "http://$hostname.lan/api/table/questionnaire?country=19,3,57&filterSet=5",
//        "http://$hostname.lan/api/table/country?years=1980-2012&country=19&filterSet=2",
//        "http://$hostname.lan/api/table/country?years=1980-2012&country=19&filterSet=5",
//        "http://$hostname.lan/api/table/country?years=1980-2012&country=19,3,57&filterSet=2",
//        "http://$hostname.lan/api/table/country?years=1980-2012&country=19,3,57&filterSet=5",
//        "http://$hostname.lan/api/chart?country=19&filterSet=2&part=1",
//        "http://$hostname.lan/api/chart?country=19&filterSet=5&part=1",
//        "http://$hostname.lan/api/chart?country=19&filterSet=2&part=3",
//        "http://$hostname.lan/api/chart?country=19&filterSet=5&part=3",
        "http://$hostname.lan/api/chart/getPanelFilters?fields=color&filters=75,76&getQuestionnaireUsages=true&ignoredElements=&part=1&questionnaire=22",
    );

    $maxSize = 0;
    foreach ($urls as $url) {
        $size = strlen($url);
        if ($size > $maxSize) {
            $maxSize = $size;
        }
    }

    echo '|_. ' . str_pad('URL', $maxSize - 1) . '|_. Time |_. SQL |' . PHP_EOL;
    foreach ($urls as $url) {
        echo '| ' . str_pad($url, $maxSize) . ' | ';

        echo `truncate -s 0 data/logs/all.log`;
        $time = trim(`{ time --format "%e" wget -q -O "/dev/null" "$url"; } 2>&1`);
        echo str_pad($time, 6, ' ', STR_PAD_LEFT) . ' | ';

        $sql = trim(`grep -cE "SELECT .*" data/logs/all.log`);
        echo str_pad($sql, 5, ' ', STR_PAD_LEFT) . ' |';

        echo PHP_EOL;
    }
}

benchmark();
