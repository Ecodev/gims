<?php

namespace Application\Model;

/**
 * QuestionType defines the possible status in a Questionnaire workflow
 */
class QuestionType extends AbstractEnum
{

    /**
     * A question which does not have any answer (used for informative text only)
     */
    public static $INFO = 'info';

    /**
     * A question without any answers, but with several sub-questions.
     * Each sub-questions may have its own type
     */
    public static $MULTI_TYPE = 'multi_type';

    /**
     * A numeric answer, eg: 123.45
     */
    public static $NUMERIC = 'numeric';

    /**
     * A textual answer: "my answer"
     */
    public static $TEXT = 'text';

    /**
     * An answer which links to a single \Application\Model\Choice object
     */
    public static $CHOICE = 'choice';

    /**
     * An answer which links to a single \Application\Model\User object
     */
    public static $USER = 'user';

}

QuestionType::initialize();
