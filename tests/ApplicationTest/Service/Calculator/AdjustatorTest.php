<?php

namespace ApplicationTest\Service\Calculator;

use Application\Model\FilterSet;

class AdjustatorTest extends AbstractCalculator
{

    /**
     * @var \Application\Model\Filter
     */
    protected $filterReference;

    /**
     * @var \Application\Model\Filter
     */
    protected $filterTarget;

    /**
     * @var \Application\Model\Filter
     */
    protected $filterChangeable;

    public function setUp()
    {
        parent::setUp();
        $this->questionnaires = [];
        $this->filterTarget = $this->getNewModelWithId('\Application\Model\Filter')->setName('Filter target');
        $this->filterReference = $this->getNewModelWithId('\Application\Model\Filter')->setName('Filter reference');
        $this->filterChangeable = $this->getNewModelWithId('\Application\Model\Filter')->setName('Filter changeable ');

        $data = [
            2000 => [
                [
                    'filter' => $this->filterTarget,
                    'value' => 0.1,
                ],
                [
                    'filter' => $this->filterReference,
                    'value' => 'formula',
                ],
                [
                    'filter' => $this->filterChangeable,
                    'value' => 0.0005,
                ],
            ],
            2002 => [
                [
                    'filter' => $this->filterReference,
                    'value' => 'formula',
                ],
                [
                    'filter' => $this->filterChangeable,
                    'value' => 0.0015,
                ],
            ],
            2005 => [
                [
                    'filter' => $this->filterTarget,
                    'value' => 0.2,
                ],
                [
                    'filter' => $this->filterReference,
                    'value' => 'formula',
                ],
                [
                    'filter' => $this->filterChangeable,
                    'value' => 0.003,
                ],
            ],
        ];

        $rule = new \Application\Model\Rule\Rule();
        $rule->setFormula('={F#' . $this->filterChangeable->getId() . ',Q#current,P#current} * 100');

        // Create Surveys and Questionnaires based on data above
        foreach ($data as $year => $d) {

            $survey = new \Application\Model\Survey();
            $survey->setCode('tst ' . $year)->setName('Test survey ' . $year)->setYear($year);
            $questionnaire = $this->getNewModelWithId('\Application\Model\Questionnaire');
            $questionnaire->setSurvey($survey)->setGeoname($this->geoname);

            foreach ($d as $dd) {

                $question = new \Application\Model\Question\NumericQuestion();
                $question->setFilter($dd['filter']);

                if ($dd['value'] == 'formula') {
                    $usage = new \Application\Model\Rule\FilterQuestionnaireUsage();
                    $usage->setFilter($this->filterReference)
                            ->setQuestionnaire($questionnaire)
                            ->setPart($this->part1)
                            ->setRule($rule)
                    ;
                } else {
                    $answer = new \Application\Model\Answer();
                    $answer->setPart($this->part1)->setQuestionnaire($questionnaire)->setQuestion($question)->setValuePercent($dd['value']);
                }

                $this->questionnaires[] = $questionnaire;
            }
        }
    }

    public function testComputeFormulaFlattenReturnsYear()
    {
        $a = new \Application\Service\Calculator\Adjustator();
        $calculator = $this->getNewJmp();
        $a->setCalculator($calculator);
        $overridenFilters = $a->findOverridenFilters($this->filterTarget, $this->filterReference, $this->filterChangeable, $this->questionnaires, $this->part1);

        $calculator->setOverridenFilters($overridenFilters);
        $actual = $calculator->computeFlattenAllYears(2000, 2005, (new FilterSet())->addFilter($this->filterReference), $this->questionnaires, $this->part1);

        $this->assertEquals(array(
            array(
                'name' => 'Filter reference',
                'id' => 19,
                'data' =>
                array(
                    0 => 0.09915096885279695,
                    1 => 0.1192283228824067,
                    2 => 0.13930567691200935,
                    3 => 0.15938303094161199,
                    4 => 0.17946038497122174,
                    5 => 0.19953773900082439,
                ),
            ),
                )
                , $actual);
    }

}
