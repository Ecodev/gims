<?php

namespace Api\Controller\Rule;

use Api\Controller\AbstractChildRestfulController;
use Application\View\Model\NumericJsonModel;
use Application\Utility;
use Application\Service\Calculator\Calculator;

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

                $ruleId = $usage->getRule()->getId();
                if (!isset($result[$partId][$ruleId])) {
                    $result[$partId][$ruleId] = $this->hydrator->extract($usage->getRule());
                }

                $value = $calculator->computeFormulaBasic($usage);
                $roundedValue = Utility::decimalToRoundedPercent($value);

                $result[$partId][$ruleId]['values'][$usage->getQuestionnaire()->getId()] = $roundedValue;
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
