<?php

namespace Application\Model;

/**
 * QuestionnaireStatus
 */
class QuestionnaireStatus extends AbstractEnum
{

    public static $NEW = 'new';
    public static $COMPLETED = 'completed';
    public static $VALIDATED = 'validated';
    public static $REJECTED = 'rejected';

}

QuestionnaireStatus::initialize();
