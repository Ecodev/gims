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
                $partId = $usage->getPart()->getId();
                if (!isset($result[$partId])) {
                    $result[$partId] = [];
                }

                $ruleName = $usage->getRule()->getName();
                if (!isset($result[$partId][$ruleName])) {
                    $result[$partId][$ruleName]['name'] = $ruleName;
                }

                $value = $calculator->computeFormulaBeforeRegression($usage);
                $roundedValue = Utility::decimalToRoundedPercent($value);

                $hydrator = new Hydrator();

                $result[$partId][$ruleName]['values'][$usage->getQuestionnaire()->getId()] = [
                    'id' => $usage->getRule()->getId(),
                    'usage' => $hydrator->extract($usage),
                    'value' => $roundedValue,
                ];
            }
        }

        // Sort by rule names and convert associative array to simple array so
        // JSON generated is an array and not an object. This is because JSON
        // objects do not guarantee order
        foreach ($result as $partId => $rules) {
            usort($rules, function($rule1, $rule2) {
                return strcmp($rule1['name'], $rule2['name']);
            });

            $result[$partId] = $rules;
        }

        return new NumericJsonModel($result);
    }

}
