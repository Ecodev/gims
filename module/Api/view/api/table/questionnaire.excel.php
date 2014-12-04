<?php

$this->workbook->getActiveSheet()->setTitle('Questionnaires');
$this->excelTable($this->workbook, $this->columns, $this->data);
