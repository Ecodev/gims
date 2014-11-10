<?php

namespace Application\Model;

/**
 * QuestionnaireStatus defines the possible status in a Questionnaire workflow
 */
class UserState extends AbstractEnum
{

    /**
     * Questionnaire is ready to be answered (or currently being answered)
     */
    public static $NEW = 0;

    /**
     * Questionnaire was fully answered and need to be validated by someone else
     */
    public static $EMAIL_CONFIRMED = 1;

}

UserState::initialize();
