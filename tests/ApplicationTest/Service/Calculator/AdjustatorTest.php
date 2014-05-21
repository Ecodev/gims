<?php

namespace ApplicationTest\Service\Calculator;

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

    /**
     * @var \Application\Model\Filter
     */
    protected $filterOther;

    /**
     * Create filters, surveys, questionnaires and answers according to supplied data
     * @param array $data
     */
    private function populateSurveys(array $data)
    {
        $this->questionnaires = [];
        $this->filterTarget = $this->getNewModelWithId('\Application\Model\Filter')->setName('Filter target');
        $this->filterReference = $this->getNewModelWithId('\Application\Model\Filter')->setName('Filter reference');
        $this->filterChangeable = $this->getNewModelWithId('\Application\Model\Filter')->setName('Filter changeable');
        $this->filterOther = $this->getNewModelWithId('\Application\Model\Filter')->setName('Filter other');

        // Reference has two children
        $this->filterReference->addChild($this->filterChangeable)->addChild($this->filterOther);

        $rule = new \Application\Model\Rule\Rule();
        $rule->setFormula('={F#' . $this->filterChangeable->getId() . ',Q#current,P#current} * 100');

        // Create Surveys and Questionnaires based on data above
        foreach ($data as $year => $values) {

            $survey = new \Application\Model\Survey();
            $survey->setCode('tst ' . $year)->setName('Test survey ' . $year)->setYear($year);
            $questionnaire = $this->getNewModelWithId('\Application\Model\Questionnaire');
            $questionnaire->setSurvey($survey)->setGeoname($this->geoname);

            foreach ($values as $filterName => $value) {

                $question = new \Application\Model\Question\NumericQuestion();
                $question->setFilter($this->$filterName);

                if ($value == 'formula') {
                    $usage = new \Application\Model\Rule\FilterQuestionnaireUsage();
                    $usage->setFilter($this->filterReference)
                            ->setQuestionnaire($questionnaire)
                            ->setPart($this->part1)
                            ->setRule($rule)
                    ;
                } else {
                    $answer = new \Application\Model\Answer();
                    $answer->setPart($this->part1)->setQuestionnaire($questionnaire)->setQuestion($question)->setValuePercent($value);
                }

                $this->questionnaires[] = $questionnaire;
            }
        }
    }

    public function findOverriddenFiltersDataProvider()
    {
        return [
            [
                'standard case should get good results',
                [
                    2000 => [
                        'filterTarget' => 0.1,
                        'filterReference' => 'formula',
                        'filterChangeable' => 0.0005,
                    ],
                    2002 => [
                        'filterReference' => 'formula',
                        'filterChangeable' => 0.0015,
                    ],
                    2005 => [
                        'filterTarget' => 0.2,
                        'filterReference' => 'formula',
                        'filterChangeable' => 0.003,
                    ],
                ],
                [
                    2 => [
                        20 => [
                            1 => 0.0009949951171875,
                        ],
                    ],
                    3 => [
                        20 => [
                            1 => 0.001388671875,
                        ],
                    ],
                    4 => [
                        20 => [
                            1 => 0.00199713134765625,
                        ],
                    ],
                ],
            ],
            [
                'if another filter prevent to reach a low target, then overrides should be 0',
                [
                    2000 => [
                        'filterTarget' => 0.01,
                        'filterChangeable' => 0.01,
                        'filterOther' => 0.01,
                    ],
                    2002 => [
                        'filterTarget' => 0.01,
                        'filterChangeable' => 0.09,
                        'filterOther' => 0.02,
                    ],
                ],
                [
                    2 => [
                        20 => [
                            1 => 0,
                        ],
                    ],
                    3 => [
                        20 => [
                            1 => 0,
                        ],
                    ],
                ],
            ],
            [
                'if a survey originally has no value, should not be used in overridden things',
                [
                    2000 => [
                        // here, only specify target, on purpose
                        'filterTarget' => 0.01,
                    ],
                    2002 => [
                        'filterTarget' => 0.01,
                        'filterChangeable' => 0.05,
                    ],
                ],
                [
                    3 => [
                        20 => [
                            1 => 0.009960937500000003,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider findOverriddenFiltersDataProvider
     */
    public function testCanFindOverriddenFilters($description, array $data, array $expected)
    {
        $this->populateSurveys($data);
        $adjustator = new \Application\Service\Calculator\Adjustator();
        $calculator = $this->getNewJmp();
        $adjustator->setCalculator($calculator);
        $overridenFilters = $adjustator->findOverriddenFilters($this->filterTarget, $this->filterReference, $this->filterChangeable, $this->questionnaires, $this->part1);

        $this->assertEquals($expected, $overridenFilters, $description);
    }

}
