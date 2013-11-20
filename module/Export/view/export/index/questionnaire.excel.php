<?php

define('ID', 0);
define('CHAPTER', 1);
define('QUESTION', 2);
define('CHOICES', 3);
define('NUM_URBAN', 4);
define('NUM_RURAL', 5);
define('NUM_TOTAL', 6);
define('TXT_URBAN', 7);
define('TXT_RURAL', 8);
define('TXT_TOTAL', 9);

/* @var $workbook \PHPExcel */
$workbook;

// headers
$row = 1;

$workbook->getActiveSheet()->mergeCells('E' . $row . ':G' . $row);
$workbook->getActiveSheet()->mergeCells('H' . $row . ':J' . $row);
$workbook->getActiveSheet()->setCellValueByColumnAndRow(NUM_URBAN, $row, 'Numerical');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(TXT_URBAN, $row, 'Text');

$row++;
$workbook->getActiveSheet()->setCellValueByColumnAndRow(ID, $row, 'ID');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(CHAPTER, $row, 'Chapter');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(QUESTION, $row, 'Question');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(CHOICES, $row, 'Choices');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(NUM_URBAN, $row, 'Urban');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(NUM_RURAL, $row, 'Rural');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(NUM_TOTAL, $row, 'Total');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(TXT_URBAN, $row, 'Urban');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(TXT_RURAL, $row, 'Rural');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(TXT_TOTAL, $row, 'Total');

foreach ($questions as $question) {
    $row++;

    switch ($question['type']) {
    case 'Chapter':
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(ID, $row, $question['id']);
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(CHAPTER, $row, $question['name']);
        break;

    case 'Numeric':
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(ID, $row, $question['id']);
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(QUESTION, $row, $question['name']);
        insertParts('num', $question, $row, $workbook, $questionnaire);
        break;

    case 'Text':
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(ID, $row, $question['id']);
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(QUESTION, $row, $question['name']);
        insertParts('txt', $question, $row, $workbook, $questionnaire);
        break;

    case 'User':
    case 'Choice':
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(ID, $row, $question['id']);
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(QUESTION, $row, $question['name']);
        insertChoices($question, $row, $workbook, $questionnaire);
        break;
    }
}

function insertParts($type, $question, $row, $workbook, $questionnaire, $choice = null)
{
    foreach ($question['parts'] as $part) {

        switch ($part['name']) {
        case 'Urban' :
            switch ($type) {
            case 'num' :
                $col = NUM_URBAN;
                break;
            case 'txt' :
                $col = TXT_URBAN;
                break;
            }
            break;
        case 'Rural' :
            switch ($type) {
            case 'num' :
                $col = NUM_RURAL;
                break;
            case 'txt' :
                $col = TXT_RURAL;
                break;
            }
            break;
        case 'Total' :
            switch ($type) {
            case 'num' :
                $col = NUM_TOTAL;
                break;
            case 'txt' :
                $col = TXT_TOTAL;
                break;
            }
            break;
        }
        $workbook->getActiveSheet()->setCellValueByColumnAndRow($col, $row, getAnswer($question, $part, $choice, $questionnaire));
    }
}

function insertChoices($question, &$row, $workbook, $questionnaire)
{
    if (isset($question['isMultiple']) && $question['isMultiple']) {
        foreach ($question['choices'] as $choice) {
            $row++;
            $workbook->getActiveSheet()->setCellValueByColumnAndRow(CHOICES, $row, $choice['name']);
            insertParts('num', $question, $row, $workbook, $questionnaire, $choice);
        }
    } else {
        insertParts('num', $question, $row, $workbook, $questionnaire);
    }
}

function getAnswer($question, $part, $choice, $questionnaire)
{

    foreach ($question['answers'] as $answer) {
        if ($answer['questionnaire']['id'] == $questionnaire->getId() &&
            (
                (isset($question['isMultiple']) && $question['isMultiple'] && $answer['valueChoice']['id'] == $choice['id'] && $answer['part']['id'] == $part['id']) ||
                ((!isset($question['isMultiple']) || !$question['isMultiple']) && $answer['part']['id'] == $part['id'])
            )
        ) {

            switch ($question['type']) {
            case 'Numeric':
                return $answer['valueAbsolute'];
                break;

            case 'Text':
                return $answer['valueText'];
                break;

            case 'User':
            case 'Choice':
                if (isset($question['isMultiple']) && $question['isMultiple']) {
                    return 1;
                } else {
                    return $answer['valuePercent'];
                }
                break;
            }
        }
    }

    return '';
}
