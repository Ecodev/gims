<?php

$this->workbook->getActiveSheet()->setTitle('Countries');
$this->excelTable($this->workbook, $this->columns, $this->data, $this->legends);
