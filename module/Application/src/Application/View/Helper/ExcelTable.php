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

        $rowSorting = [];

        // Header
        foreach ($columns as $column) {
            $rowSorting[] = $column['field'];
            if (isset($column['thematic'])) {
                $sheet->setCellValueByColumnAndRow($col, $row, $column['thematic']);
            }
            if (isset($column['part'])) {
                $sheet->setCellValueByColumnAndRow($col, $row + 1, $column['part']);
            }
            $sheet->setCellValueByColumnAndRow($col, $row + 2, isset($column['displayLong']) ? $column['displayLong'] : $column['displayName']);

            if (isset($column['filterColor'])) {
                $format = array(
                    'font' => array(
                        //  'color' => array('rgb' => 'EAEAEA')
                    ),
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => array(
                            'rgb' => str_replace('#', '', $column['filterColor']),
                        ),
                    ),
                );
                $sheet->getStyleByColumnAndRow($col, $row + 2)->applyFromArray($format);
            }

            $col++;
        }
        $row += 3;

        // Actual data
        foreach ($data as $dataRow) {
            $col = 0;
            foreach ($rowSorting as $field) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $dataRow[$field]);
            }
            $row++;
        }
    }

    /**
     * Write a simple table to Excel file
     */
    public function __invoke(\PHPExcel $workbook, array $columns, array $data)
    {
        $dataSheet = $workbook->getActiveSheet();
        $this->writeData($dataSheet, $columns, $data);

    }

}
