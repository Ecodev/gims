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
$workbook->getActiveSheet()
    ->setCellValueByColumnAndRow(CHAPTER, $row, 'Chapter');
$workbook->getActiveSheet()
    ->setCellValueByColumnAndRow(QUESTION, $row, 'Question');
$workbook->getActiveSheet()
    ->setCellValueByColumnAndRow(CHOICES, $row, 'Choices');
$workbook->getActiveSheet()->setCellValueByColumnAndRow(PART, $row, 'Part');

$questionnaireCol = ANSWER;

foreach ($questionnaires as $questionnaire) {
    $row = 2;

    $workbook->getActiveSheet()
        ->setCellValueByColumnAndRow($questionnaireCol, $row - 1, $questionnaire->getGeoname()
        ->getCountry()->getIso3());
    $workbook->getActiveSheet()
        ->setCellValueByColumnAndRow($questionnaireCol, $row, $questionnaire->getGeoname()
        ->getName());

    foreach ($questions as $question) {
        $row++;

        // Question labels (only if first questionnaire)
        if ($questionnaireCol == ANSWER) {
            if ($question['dtype'] == 'chapter') {
                $labelCol = CHAPTER;
            } else {
                $labelCol = QUESTION;
            }
            $workbook->getActiveSheet()
                ->setCellValueByColumnAndRow(ID, $row, $question['id']);
            $workbook->getActiveSheet()
                ->setCellValueByColumnAndRow($labelCol, $row, $question['name']);
        }

        switch ($question['dtype']) {
        case 'numericquestion':
        case 'textquestion':
            insertParts($question, $questionnaireCol, $row, $workbook, $questionnaire);
            break;

        case 'choicequestion':
            insertChoices($question, $questionnaireCol, $row, $workbook, $questionnaire);
            break;
        }
    }
    $questionnaireCol++;
}

function insertParts($question, $col, &$row, $workbook, $questionnaire, $choice = null)
{
    foreach ($question['parts'] as $key => $part) {
        $workbook->getActiveSheet()->setCellValueByColumnAndRow(PART, $row, $part['name']);
        if (isset($question['answers'][$part['id']][$questionnaire->getId()])) {
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($col, $row, getAnswer($question, $part, $choice, $questionnaire->getId()));
        }
        $nbParts = count($question['parts']);
        if ($nbParts > 1 && $key < $nbParts - 1) {
            $row++;
        }
    }
}

function insertChoices($question, $col, &$row, $workbook, $questionnaire)
{
    if (isset($question['isMultiple']) && $question['isMultiple']) {
        foreach ($question['choices'] as $choice) {
            $row++;
            $workbook->getActiveSheet()->setCellValueByColumnAndRow(CHOICES - 1, $row, $choice['id']);
            $workbook->getActiveSheet()->setCellValueByColumnAndRow(CHOICES, $row, $choice['name']);
            insertParts($question, $col, $row, $workbook, $questionnaire, $choice);
        }
    } else {
        insertParts($question, $col, $row, $workbook, $questionnaire);
    }
}

function getAnswer($question, $part, $choice, $questionnaireId)
{
    foreach ($question['answers'][$part['id']][$questionnaireId] as $answer) {
        if (((isset($question['isMultiple']) && $question['isMultiple'] && $answer['valueChoice']['id'] == $choice['id'])
            || ((!isset($question['isMultiple']) || !$question['isMultiple'])))
        ) {
            switch ($question['dtype']) {

            case 'numericquestion':
                return $answer['valueAbsolute'];

            case 'textquestion':
                return $answer['valueText'];

            case 'userquestion':
            case 'choicequestion':
                if (isset($question['isMultiple']) && $question['isMultiple']) {
                    return 1;
                } else {
                    return $answer['valuePercent'];
                }

            }
        }
    }

    return '';
}
