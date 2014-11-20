<?php

namespace Application\View\Helper;

class ExcelTable extends \Zend\View\Helper\AbstractHtmlElement
{

    /**
     * Write data table on given sheet
     * @param \PHPExcel_Worksheet $sheet
     * @param array $columns
     * @param array $data
     */
    private function writeData(\PHPExcel_Worksheet $sheet, array $columns, array $data)
    {
        $col = 0;
        $row = 1;

        // Header
        foreach ($columns as $column) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $column);
        }
        $row++;

        // Actual data
        foreach ($data as $dataRow) {
            $col = 0;
            foreach ($dataRow as $value) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $value);
            }
            $row++;
        }
    }

    /**
     * Write legends on given sheet
     * @param \PHPExcel_Worksheet $sheet
     * @param array $legends
     */
    private function writeLegends(\PHPExcel_Worksheet $sheet, array $legends)
    {
        $row = 1;

        foreach ($legends as $legend) {
            $sheet->setCellValueByColumnAndRow(0, $row, $legend['short']);
            $sheet->setCellValueByColumnAndRow(1, $row, $legend['long']);
            $row++;
        }
    }

    /**
     * Write a simple table to Excel file
     */
    public function __invoke(\PHPExcel $workbook, array $columns, array $data, array $legends)
    {
        $dataSheet = $workbook->getActiveSheet();
        $this->writeData($dataSheet, $columns, $data);

        $legendSheet = $workbook->createSheet();
        $legendSheet->setTitle('Legends');
        $this->writeLegends($legendSheet, $legends);
    }

}
