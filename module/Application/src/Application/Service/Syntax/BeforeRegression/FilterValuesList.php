<?php

namespace Application\Service\Syntax\BeforeRegression;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Syntax\Parser;

/**
 * Replace {F#12,Q#all} with a list of Filter values for all questionnaires
 */
class FilterValuesList extends AbstractToken
{

    public function getPattern()
    {
        return '/\{F#(\d+|current),Q#all\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules)
    {
        $filterId = $this->getFilterId($matches[1], $usage);

        $questionnaires = $calculator->getQuestionnaireRepository()->getAllForComputing($usage->getQuestionnaire()->getGeoname());
        $data = $calculator->computeFilterForAllQuestionnaires($filterId, $questionnaires, $usage->getPart()->getId());

        $values = array();
        foreach ($data['values'] as $v) {
            if (!is_null($v)) {
                $values[] = $v;
            }
        }

        $values = '{' . implode(', ', $values) . '}';

        return $values;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'regressionFilterValuesList',
            'filter' => $parser->getFilterName($matches[1]),
        ];
    }

}
