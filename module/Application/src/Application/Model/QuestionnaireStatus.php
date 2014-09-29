<?php

namespace Application\Model;

/**
 * QuestionnaireStatus defines the possible status in a Questionnaire workflow
 */
class QuestionnaireStatus extends AbstractEnum
{

    /**
     * Questionnaire is ready to be answered (or currently being answered)
     */
    public static $NEW = 'new';

    /**
     * Questionnaire was fully answered and need to be validated by someone else
     */
    public static $COMPLETED = 'completed';

    /**
     * Questionnaire is validated and cannot be modified anymore
     */
    public static $VALIDATED = 'validated';

    /**
     * Questionnaire is publicly available to anyone for computation.
     */
    public static $PUBLISHED = 'published';

    /**
     * Questionnaire was not validated, but rejected.
     */
    public static $REJECTED = 'rejected';

}

QuestionnaireStatus::initialize();
