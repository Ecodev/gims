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
                $this->applyFormat($sheet, $col, $row, $column, 'thematic');
            }

            if (isset($column['part'])) {
                $sheet->setCellValueByColumnAndRow($col, $row + 1, $column['part']);
            }

            $sheet->setCellValueByColumnAndRow($col, $row + 2, isset($column['displayLong']) ? $column['displayLong'] : $column['displayName']);
            $this->applyFormat($sheet, $col, $row + 2, $column, 'filter');

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

    private function applyFormat($sheet, $col, $row, $column, $type)
    {
        $mainColor = isset($column[$type . 'Color']) ? str_replace('#', '', $column[$type . 'Color']) : null;
        $textColor = isset($column[$type . 'TextColor']) ? str_replace('#', '', $column[$type . 'TextColor']) : null;

        if (!$mainColor && !$textColor) {
            return;
        };

        $format = [];
        if ($textColor) {
            $format['font'] = [
                'color' => [
                    'rgb' => $textColor,
                ],
            ];
        }

        if ($mainColor) {
            $format['fill'] = [
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => [
                    'rgb' =>  $mainColor,
                ],
            ];
        }

        $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($format);
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
