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
        return [
            'Application\Model\Question\Chapter' => self::$CHAPTER,
            'Application\Model\Question\NumericQuestion' => self::$NUMERIC,
            'Application\Model\Question\TextQuestion' => self::$TEXT,
            'Application\Model\Question\ChoiceQuestion' => self::$CHOICE,
            'Application\Model\Question\UserQuestion' => self::$USER,
        ];
    }

    /**
     * Return the class name of the given QuestionType
     * @param \Application\Model\QuestionType $type
     * @return string
     * @throws \Exception
     */
    public static function getClass(QuestionType $type)
    {
        $className = array_search($type, self::getMapping());
        if (!$className) {
            throw new \Exception('Unsupported QuestionType: ' . $type);
        }

        return $className;
    }

    /**
     * Returns the QuestionType corresponding to the className
     * @param string $className
     * @return self
     * @throws \Exception
     */
    public static function getType($className)
    {
        // here we need to take into account Doctrine Proxy subclass, so we need
        // to assume that the given classname may be a child of our own class
        foreach (self::getMapping() as $class => $type) {
            if ($class == $className || is_subclass_of($className, $class)) {
                return $type;
            }
        }

        throw new \Exception('Unsupported QuestionType for class name: ' . $className);
    }
}

QuestionType::initialize();
