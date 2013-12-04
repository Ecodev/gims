<?php

define('ID', 0);
define('CHAPTER', 1);
define('QUESTION', 2);
define('CHOICES', 3);
define('PART', 4);
define('ANSWER', 5);

/* @var $questionnaire Application\Model\Questionnaire */
/* @var $workbook \PHPExcel */
$workbook;

// headers
$row = 2;
$workbook->getActiveSheet()->setCellValueByColumnAndRow(ID, $row, 'ID');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(CHAPTER, $row, 'Chapter');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(QUESTION, $row, 'Question');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(CHOICES, $row, 'Choices');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(PART, $row, 'Part');

$questionnaireCol = ANSWER;

foreach ($questionnaires as $questionnaire) {
    $row = 2;

    $workbook->getActiveSheet()->setCellValueByColumnAndRow($questionnaireCol, $row-1, $questionnaire->getGeoname()->getCountry());
    $workbook->getActiveSheet()->setCellValueByColumnAndRow($questionnaireCol, $row, $questionnaire->getGeoname()->getName());

    foreach ($questions as $question) {
        $row++;

        // Question labels (only if first questionnaire)
        if($questionnaireCol == ANSWER) {
            if ($question['type'] == 'Chapter') {
                $labelCol = CHAPTER;
            } else {
                $labelCol = QUESTION;
            }
            $workbook->getActiveSheet()->setCellValueByColumnAndRow(ID, $row, $question['id']);
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($labelCol, $row, $question['name']);
        }

        switch ($question['type']) {
        case 'Numeric':
        case 'Text':
            insertParts($question, $questionnaireCol, $row, $workbook, $questionnaire);
            break;

        case 'Choice':
            insertChoices($question, $questionnaireCol, $row, $workbook, $questionnaire);
            break;
        }
    }
    $questionnaireCol++;
}

function insertParts($question, $col, &$row, $workbook, $questionnaire, $choice = null)
{
    foreach ($question['parts'] as $key =>  $part) {
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(PART, $row, $part['name']);
        $workbook->getActiveSheet()->setCellValueByColumnAndRow($col, $row, getAnswer($question, $part, $choice, $questionnaire));
        $nbParts = count($question['parts']);
        if ($nbParts > 1 && $key < $nbParts-1) {
            $row++;
        }
    }
}

function insertChoices($question, $col,  &$row, $workbook, $questionnaire)
{
    if (isset($question['isMultiple']) && $question['isMultiple']) {
        foreach ($question['choices'] as $choice) {
            $row++;
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $choice['name']);
            insertParts($question, $col, $row, $workbook, $questionnaire, $choice);
        }
    } else {
        insertParts($question, $col, $row, $workbook, $questionnaire);
    }
}

function getAnswer($question, $part, $choice, $questionnaire)
{

    foreach ($question['answers'] as $answer) {
        if ($answer['questionnaire']['id'] == $questionnaire->getId() &&
            ((isset($question['isMultiple']) && $question['isMultiple'] && $answer['valueChoice']['id'] == $choice['id'] && $answer['part']['id'] == $part['id']) ||
            ((!isset($question['isMultiple']) || !$question['isMultiple']) && $answer['part']['id'] == $part['id']))
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
