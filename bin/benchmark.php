<?php

/**
 * CLI script to benchmark GIMS API.
 * Takes the number of repetition as argument (default to 1)
 */

/**
 * Compute average
 * @param array $data
 * @return float
 */
function average(array $data)
{
    return array_sum($data) / count($data);
}

function getUrls()
{
    $hostname = basename(getcwd());

    $southernAsia = 7;
    $bangladesh = '1210997';
    $world = 26;
    $bangladeshGermanyAfghanistan = '1210997,2921044,1149361';
    $questionnairesForBangladesh = '2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23';
    $questionnairesForBangladeshGermanyAfghanistan = '2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58';
    $water = '75,76,77,78,79';
    $sanitation = '166,167,168,169,170,171';
    $urls = array(
        'filter/getComputedFilters Water Bangladesh                   ' => "http://$hostname.lan/api/filter/getComputedFilters?filters=3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74&questionnaires=$questionnairesForBangladesh",
        'table/questionnaire Water Bangladesh                         ' => "http://$hostname.lan/api/table/questionnaire?filters=$water&questionnaires=$questionnairesForBangladesh",
        'table/questionnaire Sanitation Bangladesh                    ' => "http://$hostname.lan/api/table/questionnaire?filters=$sanitation&questionnaires=$questionnairesForBangladesh",
        'table/questionnaire Water Bangladesh,Germany,Afghanistan     ' => "http://$hostname.lan/api/table/questionnaire?filters=$water&questionnaires=$questionnairesForBangladeshGermanyAfghanistan",
        'table/questionnaire Sanitation Bangladesh,Germany,Afghanistan' => "http://$hostname.lan/api/table/questionnaire?filters=$sanitation&questionnaires=$questionnairesForBangladeshGermanyAfghanistan",
        'table/country Water Bangladesh                               ' => "http://$hostname.lan/api/table/country?filters=$water&geonames=$bangladesh&years=1980-2015",
        'table/country Sanitation Bangladesh                          ' => "http://$hostname.lan/api/table/country?filters=$sanitation&geonames=$bangladesh&years=1980-2015",
        'table/country Water Bangladesh,Germany,Afghanistan           ' => "http://$hostname.lan/api/table/country?filters=$water&geonames=$bangladeshGermanyAfghanistan&years=1980-2015",
        'table/country Sanitation Bangladesh,Germany,Afghanistan      ' => "http://$hostname.lan/api/table/country?filters=$sanitation&geonames=$bangladeshGermanyAfghanistan&years=1980-2015",
        'table/country Water Southern Asia                            ' => "http://$hostname.lan/api/table/country?filters=$water&geonames=$southernAsia&years=1980-2015",
        'table/country Sanitation Southern Asia                       ' => "http://$hostname.lan/api/table/country?filters=$sanitation&geonames=$southernAsia&years=1980-2015",
        'table/country Water World                                    ' => "http://$hostname.lan/api/table/country?filters=$water&geonames=$world&years=1980-2015",
        'table/country Sanitation World                               ' => "http://$hostname.lan/api/table/country?filters=$sanitation&geonames=$world&years=1980-2015",
        'chart/getSeries Water Bangladesh Urban                       ' => "http://$hostname.lan/api/chart/getSeries?filters=$water&geonames=$bangladesh&part=1",
        'chart/getSeries Sanitation Bangladesh Urban                  ' => "http://$hostname.lan/api/chart/getSeries?filters=$sanitation&geonames=$bangladesh&part=1",
        'chart/getSeries Water Bangladesh Total                       ' => "http://$hostname.lan/api/chart/getSeries?filters=$water&geonames=$bangladesh&part=3",
        'chart/getSeries Sanitation Bangladesh Total                  ' => "http://$hostname.lan/api/chart/getSeries?filters=$sanitation&geonames=$bangladesh&part=3",
        'chart/getSeries Water Southern Asia Total                    ' => "http://$hostname.lan/api/chart/getSeries?filters=$water&geonames=$southernAsia&part=3",
        'chart/getSeries Sanitation Southern Asia Total               ' => "http://$hostname.lan/api/chart/getSeries?filters=$sanitation&geonames=$southernAsia&part=3",
        'chart/getSeries Water World Total                            ' => "http://$hostname.lan/api/chart/getSeries?filters=$water&geonames=$world&part=3",
        'chart/getSeries Sanitation World Total                       ' => "http://$hostname.lan/api/chart/getSeries?filters=$sanitation&geonames=$world&part=3",
        'chart/getPanelFilters Water CEN11                            ' => "http://$hostname.lan/api/chart/getPanelFilters?fields=color&filters=75,76&getQuestionnaireUsages=true&ignoredElements=&part=1&questionnaire=22",
        'questionnaireUsage/compute Bangladesh                        ' => "http://$hostname.lan/api/questionnaireUsage/compute?questionnaires=$questionnairesForBangladesh",
    );

    return $urls;
}

/**
 * Do the benchmark
 */
function benchmark($repetition)
{
    $urls = getUrls();
    $maxSize = 0;
    foreach ($urls as $label => $url) {
        $size = strlen($label);
        if ($size > $maxSize) {
            $maxSize = $size;
        }
    }

    echo "Repetitions: $repetition" . PHP_EOL;
    echo '|_. ' . str_pad('URL', $maxSize - 1) . '|_. Time |_. SQL |' . PHP_EOL;
    foreach ($urls as $label => $url) {
        echo '| ' . str_pad($label, $maxSize) . ' | ';

        $stats = [];
        for ($i = 0; $i < $repetition; $i++) {
            echo `> data/logs/all.log`;
            $time = trim(`{ time -p wget -q -O "/dev/null" "$url"; } 2>&1`);
            preg_match('/\d+(.\d+)/', $time, $m);
            $stats['time'][] = $m[0];
            $stats['sql'][] = trim(`grep -cE "SELECT .*" data/logs/all.log`);
        }

        echo str_pad(average($stats['time']), 6, ' ', STR_PAD_LEFT) . ' | ';
        echo str_pad(average($stats['sql']), 5, ' ', STR_PAD_LEFT) . ' |';

        echo PHP_EOL;
    }
}

$repetition = @$argv[1] ? : 1;
if ($repetition == 'url') {
    var_export(getUrls());
} else {
    benchmark((int) $repetition);
}
