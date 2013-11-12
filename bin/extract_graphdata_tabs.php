<?php

/**
 * This script extract data from GraphData_W/GraphData_S for all known country files
 */
require_once('vendor/phpoffice/phpexcel/Classes/PHPExcel.php');
$countries = require (__DIR__ . '/countries.php');

/**
 * Process all country files
 * @param array $countries
 */
function doAllCountries(array $countries)
{
    foreach ($countries as $key => $country) {
        $filename = $country['path'];

        if (!$filename) {
            "SKIP: mising file for " . $country['name'] . PHP_EOL;
            continue;
        }
        echo $filename . PHP_EOL;


        $cmd = 'php ' . __FILE__ . ' "' . $key . '"';
        echo $cmd;
        echo `$cmd`;

        gc_collect_cycles();
    }
}

/**
 * Extract data from GraphData_W/GraphData_S for one country file
 * @param array $country
 */
function doOneCountry(array $country)
{

    $filename = $country['path'];
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
    $sheetNamesToImport = array_merge(array_keys($def), array('Tables_W', 'Tables_S', 'Population'));

    $superdata = array(
        'Water' => array(),
        'Sanitation' => array(),
    );

    echo $filename . PHP_EOL;
    $reader = \PHPExcel_IOFactory::createReaderForFile($filename);
    $reader->setReadDataOnly(true);
    $reader->setLoadSheetsOnly($sheetNamesToImport);
    $wb = $reader->load($filename);

    foreach (array_keys($def) as $i => $sheetName) {

        $wb->setActiveSheetIndex($i);
        $sheet = $wb->getSheet($i);

        foreach ($def[$sheetName]['rows'] as $row) {
            $cell = $sheet->getCellByColumnAndRow(reset($def[$sheetName]['cols']), $row);
            $code = getCalculatedValueSafely($cell);
            unset($cell);

            if (!$code) {
                break;
            }

            var_dump($code);

            $data = [$country['name'], $country['iso3']];
            foreach ($def[$sheetName]['cols'] as $col) {

                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $data[] = getCalculatedValueSafely($cell);
                unset($cell);
            }
            $superdata[$def[$sheetName]['suffix']][] = $data;
        }
        unset($sheet);
    }
    unset($wb);
    unset($reader);

    $dir = 'extracted_data/';
    @mkdir($dir);
    $outputname = $dir . $country['name'];

    $res = var_export($superdata, true);
    file_put_contents($outputname . '.php', $res);
    echo 'READING DONE' . PHP_EOL;
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
    try {
        return $cell->getCalculatedValue();
    } catch (\PHPExcel_Exception $exception) {

        // Fallback on cached result
        if (preg_match('/(Cyclic Reference in Formula|Formula Error)/', $exception->getMessage())) {
            $value = $cell->getOldCalculatedValue();

            return $value == \PHPExcel_Calculation_Functions::NA() ? null : $value;
        } else {
            // Forward exception
            throw $exception;
        }
    }
}

if (isset($argv[1])) {
    doOneCountry($countries[$argv[1]]);
} else {
    doAllCountries($countries);
}
