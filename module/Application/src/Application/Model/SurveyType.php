<?php

namespace Application\Model;

/**
 * SurveyType defines the possible types of survey
 */
class SurveyType extends AbstractEnum
{

    /**
     * A survey with mixed type of question (not only numeric)
     */
    public static $GLAAS = 'glaas';

    /**
     * A survey with strictly only numeric question for population coverage
     */
    public static $JMP = 'jmp';

    /**
     * Same as JMP but specific for NSA people, that means equipement count and people per equipement questions
     */
    public static $NSA = 'nsa';

}

SurveyType::initialize();
