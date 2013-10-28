<?php

$col = 0;
$row = 1;
$sheet = $this->workbook->getActiveSheet();

// Header
foreach ($this->columns as $column) {
    $sheet->setCellValueByColumnAndRow($col++, $row, $column);
}
$row++;

// Actual data
foreach ($this->data as $dataRow) {
    $col = 0;
    foreach ($dataRow as $value) {
        $sheet->setCellValueByColumnAndRow($col++, $row, $value);
    }
    $row++;
}
