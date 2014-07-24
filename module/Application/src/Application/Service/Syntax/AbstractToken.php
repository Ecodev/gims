<?php

namespace Application\Service\Syntax;

abstract class AbstractToken
{

    /**
     * Returns the name according to 'current' syntax
     * @param string|integer $filterId
     * @param \Application\Service\Parser $parser
     * @return string
     */
    protected function getFilterName($filterId, Parser $parser)
    {
        if ($filterId == 'current') {
            return $filterId;
        } else {
            return $parser->getFilterRepository()->findOneById($filterId)->getName();
        }
    }

    /**
     * Returns the color according to 'current' syntax
     * @param string|integer $filterId
     * @param \Application\Service\Parser $parser
     * @return string
     */
    protected function getFilterColor($filterId, Parser $parser)
    {
        if ($filterId == 'current') {
            return null;
        } else {
            return $parser->getFilterRepository()->findOneById($filterId)->getColor();
        }
    }

    /**
     * Returns the name according to 'current' syntax
     * @param string|integer $questionnaireId
     * @param \Application\Service\Parser $parser
     * @return string
     */
    protected function getQuestionnaireName($questionnaireId, Parser $parser)
    {
        if ($questionnaireId == 'current') {
            return $questionnaireId;
        } else {
            return $parser->getQuestionnaireRepository()->findOneById($questionnaireId)->getName();
        }
    }

    /**
     * Returns the name according to 'current' syntax
     * @param string|integer $partId
     * @param \Application\Service\Parser $parser
     * @return string
     */
    protected function getPartName($partId, Parser $parser)
    {
        if ($partId == 'current') {
            return $partId;
        } else {
            return $parser->getPartRepository()->findOneById($partId)->getName();
        }
    }

    /**
     * Return the regexp pattern used for this syntax
     * @return string
     */
    abstract public function getPattern();

    /**
     * Returns an array representing the token
     * @param array $matches
     * @param \Application\Service\Syntax\Parser $parser
     * @return array
     */
    abstract public function getStructure(array $matches, Parser $parser);
}
