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
    public static $CHAPTER = 'Chapter';


    /**
     * A numeric answer, eg: 123.45
     */
    public static $NUMERIC = 'Numeric';

    /**
     * A textual answer: "my answer"
     */
    public static $TEXT = 'Text';

    /**
     * An answer which links to a single \Application\Model\Question\Choice object
     */
    public static $CHOICE = 'Choice';

    /**
     * An answer which links to a single \Application\Model\User object
     */
    public static $USER = 'User';

    private static function getMapping()
    {
        return array(
            'Application\Model\Question\Chapter' => self::$CHAPTER,
            'Application\Model\Question\NumericQuestion' => self::$NUMERIC,
            'Application\Model\Question\TextQuestion' => self::$TEXT,
            'Application\Model\Question\ChoiceQuestion' => self::$CHOICE,
            'Application\Model\Question\UserQuestion' => self::$USER,
        );
    }

    public static function getClass(QuestionType $type)
    {
        $className = array_search($type, self::getMapping());
        if (!$className) {
            throw new \Exception('Unsupported QuestionType: ' . $type);
        }

        return $className;
    }

    public static function getType($className)
    {
        $type = @self::getMapping()[$className];
        if (!$type) {
            throw new \Exception('Unsupported QuestionType for class name: ' . $className);
        }

        return $type;
    }

}

QuestionType::initialize();
