<?php

/**
 * This script extract data from GraphData_W/GraphData_S for all known country files
 */
require __DIR__ . '/../htdocs/index.php';
$countries = require (__DIR__ . '/countries.php');

// Enable cyclic computation
PHPExcel_Calculation::getInstance()->cyclicFormulaCount = 100;

/**
 * Process all country files
 * @param array $countries
 */
function doAllCountries(array $countries)
{
    foreach ($countries as $country) {
        $filename = $country['path'];

        if (!$filename) {
            "SKIP: mising file for " . $country['name'] . PHP_EOL;
            continue;
        }

        $cmd = 'php ' . __FILE__ . ' "' . $country['name'] . '"';
        echo $cmd . PHP_EOL;
        echo `$cmd`;
    }
}

/**
 * Extract data from GraphData_W/GraphData_S for one country file
 * @param array $country
 */
function doOneCountry(array $country)
{
    $filename = $country['path'];
    $sheetNamesToImport = array_merge(array('Estimates', 'GraphData_W', 'GraphData_S', 'Tables_W', 'Tables_S', 'Population'));

    echo $filename . PHP_EOL;
    $reader = \PHPExcel_IOFactory::createReaderForFile($filename);
    $reader->setReadDataOnly(true);
    $reader->setLoadSheetsOnly($sheetNamesToImport);
    $wb = $reader->load($filename);

    doOneCountryQuestionnaire($country, $wb);
    doOneCountryCountry($country, $wb);
}

function doOneCountryQuestionnaire(array $country, \PHPExcel $wb)
{
    $def = array(
        'GraphData_W' => array(
            'suffix' => 'Water',
            'rows' => range(5, 44),
            'cols' => range(1, 20),
        ),
        'GraphData_S' => array(
            'suffix' => 'Sanitation',
            'rows' => range(5, 44),
            'cols' => range(1, 26),
        ),
    );

    $superdata = array(
        'Water' => array(),
        'Sanitation' => array(),
    );

    foreach (array_keys($def) as $sheetName) {

        $sheet = $wb->getSheetByName($sheetName);

        foreach ($def[$sheetName]['rows'] as $row) {
            $cell = $sheet->getCellByColumnAndRow(reset($def[$sheetName]['cols']), $row);
            $code = getCalculatedValueSafely($cell);
            unset($cell);

            if (!$code) {
                break;
            }

            echo $def[$sheetName]['suffix'] . ' ' . $code . PHP_EOL;

            $data = [$country['name'], $country['iso3']];
            foreach ($def[$sheetName]['cols'] as $col) {

                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $data[] = getCalculatedValueSafely($cell);
            }
            $superdata[$def[$sheetName]['suffix']][] = $data;
        }
    }

    echo 'READING DONE' . PHP_EOL;

    $dir = 'expected/questionnaire/';
    @mkdir($dir, 0777, true);
    $outputname = $dir . $country['name'];

    $phpCode = '<?php' . PHP_EOL . 'return ' . var_export($superdata, true) . ';' . PHP_EOL;
    file_put_contents($outputname . '.php', $phpCode);

    $superdata = sanitizeQuestionnaires($superdata);
    allToCsv($outputname, $superdata);
}

function doOneCountryCountry(array $country, \PHPExcel $wb)
{
    $defs = array(
        'Water' => array(
            'rows' => range(6, 38),
            'cols' => range(0, 15),
        ),
        'Sanitation' => array(
            'rows' => range(6, 38),
            'cols' => array_merge(array(0), range(16, 30)),
        ),
    );

    $superdata = array(
        'Water' => array(),
        'Sanitation' => array(),
    );

    $sheet = $wb->getSheetByName('Estimates');
    foreach ($defs as $type => $def) {
        foreach ($def['rows'] as $row) {

            $data = [$country['name'], $country['iso3']];
            foreach ($def['cols'] as $col) {

                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $value = getCalculatedValueSafely($cell);
                if ($col != 0 && $cell->getDataType() != \PHPExcel_Cell_DataType::TYPE_FORMULA) {
                    $value = 'HARDCODED ' . $cell->getCoordinate() . ' = ' . $value;
                }

                $data[] = $value;
            }
            echo $type . ' ' . $data[2] . PHP_EOL;
            $superdata[$type][] = $data;
        }
    }

    echo 'READING DONE' . PHP_EOL;

    $dir = 'expected/country/';
    @mkdir($dir, 0777, true);
    $outputname = $dir . $country['name'];

    $phpCode = '<?php' . PHP_EOL . 'return ' . var_export($superdata, true) . ';' . PHP_EOL;
    file_put_contents($outputname . '.php', $phpCode);
    allToCsv($outputname, $superdata);
}

/**
 * Clean up superdata structure
 * @param array $superdata
 * @return array
 */
function sanitizeQuestionnaires(array $superdata)
{
    // Build a list of questionnaire across Water and Sanitation
    $exhaustiveList = reset($superdata);
    foreach (end($superdata) as $i => $sanitationRow) {
        if ($sanitationRow[2] != $exhaustiveList[$i][2] || $sanitationRow[3] != $exhaustiveList[$i][3]) {
            $exhaustiveList[] = $sanitationRow;
        }
    }

    // Ensure that questionnaires are liste both in Water and Sanitation
    foreach ($superdata as $name => &$originalRows) {

        $newRows = array();

        $i = 0;
        $j = 0;
        while ($i < count($exhaustiveList) || $j < count($originalRows)) {
            if (@$exhaustiveList[$i] && @$originalRows[$j] && $exhaustiveList[$i][2] == $originalRows[$j][2] && $exhaustiveList[$i][3] == $originalRows[$j][3]) {
                $newRows[] = $originalRows[$j];
                $i++;
                $j++;
            } elseif (@$exhaustiveList[$i]) {

                // If questionnaire is not in the list, inject empty entry
                $newRows[] = array(
                    $exhaustiveList[$i][0],
                    $exhaustiveList[$i][1],
                    $exhaustiveList[$i][2],
                    $exhaustiveList[$i][3],
                );
                $i++;
            } elseif (@$originalRows[$j]) {
                $newRows[] = $originalRows[$j];
                $j++;
            }
        }

        $originalRows = $newRows;
    }

    $importer = new Application\Service\Importer\Jmp();
    foreach ($superdata as $name => &$originalRows) {
        foreach ($originalRows as &$row) {
            $code = $row[2];
            $year = $row[3];
            $row[2] = $importer->standardizeSurveyCode($code, $year);
        }
    }

    return $superdata;
}

function allToCsv($outputname, $superdata)
{
    foreach ($superdata as $name => $data) {
        toCsv($outputname . ' - ' . $name, $data);
    }
}

/**
 * Write extracted data to CSV file
 * @param string $name
 * @param array $data
 */
function toCsv($name, array $data)
{
    $col = 0;
    $row = 1;

    $filename = "$name.csv";
    echo "WRITING $filename" . PHP_EOL;

    if ($data) {
        $workbook = new \PHPExcel();
        $sheet = $workbook->getActiveSheet();

        foreach ($data as $dataRow) {
            $col = 0;
            foreach ($dataRow as $value) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $value);
            }
            $row++;
        }

        $objWriter = new \PHPExcel_Writer_CSV($workbook);
        $objWriter->save($filename);
    } else {
        file_put_contents($filename, '');
    }
    echo "DONE WRITING $filename" . PHP_EOL;
}

function getCalculatedValueSafely(\PHPExcel_Cell $cell)
{
    $value = null;
    try {
        $value = $cell->getCalculatedValue();
    } catch (\PHPExcel_Exception $exception) {

        // Fallback on cached result
        if (preg_match('/(Cyclic Reference in Formula|Formula Error)/', $exception->getMessage())) {
            $value = $cell->getOldCalculatedValue();
        } else {
            // Forward exception
            throw $exception;
        }
    }

    if ($value == \PHPExcel_Calculation_Functions::NA() || $value == \PHPExcel_Calculation_Functions::DIV0()) {
        return null;
    } else {
        return $value;
    }
}

if (isset($argv[1])) {
    $onlyThose = $argv;
    array_shift($onlyThose);

    foreach ($countries as $country) {
        if (in_array($country['name'], $onlyThose)) {
            doOneCountry($country);
        }
    }
} else {
    doAllCountries($countries);
}
