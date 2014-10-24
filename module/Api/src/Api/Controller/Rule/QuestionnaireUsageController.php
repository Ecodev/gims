<?php

namespace Api\Controller\Rule;

use Api\Controller\AbstractChildRestfulController;
use Application\View\Model\NumericJsonModel;
use Application\Utility;
use Application\Service\Calculator\Calculator;
use Application\Service\Hydrator;

class QuestionnaireUsageController extends AbstractChildRestfulController
{

    public function computeAction()
    {
        $questionnaireIds = array_filter(explode(',', $this->params()->fromQuery('questionnaires')));
        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findById($questionnaireIds);

        $calculator = new Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());

        $result = [];
        foreach ($questionnaires as $questionnaire) {
            foreach ($questionnaire->getQuestionnaireUsages() as $usage) {

                $ruleName = $usage->getRule()->getName();
                if (!isset($result[$ruleName])) {
                    $result[$ruleName]['name'] = $ruleName;
                }

                $value = $calculator->computeFormulaBeforeRegression($usage);
                $roundedValue = Utility::decimalToRoundedPercent($value);

                $hydrator = new Hydrator();

                $result[$ruleName]['values'][$usage->getQuestionnaire()->getId()][$usage->getPart()->getId()] = [
                    'id' => $usage->getRule()->getId(),
                    'usage' => $hydrator->extract($usage, array('thematicFilter')),
                    'value' => $roundedValue,
                ];
            }
        }

        // Sort by rule names and convert associative array to simple array so
        // JSON generated is an array and not an object. This is because JSON
        // objects do not guarantee order
        uksort($result, function ($name1, $name2) {
            return strcmp($name1, $name2);
        });

        $finalResult = [];
        foreach ($result as $rule) {
            $finalResult[] = $rule;
        }

        return new NumericJsonModel($finalResult);
    }

}
