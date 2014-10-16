<?php

namespace ApplicationTest\Service\Calculator;

use Application\Model\Part;

/**
 * @group Calculator
 */
class AggregatorTest extends AbstractCalculator
{

    /**
     * @return \Application\Service\Calculator\Calculator
     */
    private function getStubCalculator()
    {
        $stubCalculator = $this->getMock('\Application\Service\Calculator\Calculator', array('computeFilterForAllQuestionnaires', 'computeFlattenAllYears'), array(), '', false);
        $stubCalculator->expects($this->any())
                ->method('computeFilterForAllQuestionnaires')
                ->will($this->returnCallback(function($filterId, array $questionnaires, $partId) {
                            $id = $questionnaires[0]->getId();
                            if ($id == 1) {
                                return [
                                    'values' => [
                                        1 => 0.5,
                                    ],
                                    'count' => 1,
                                    'years' => [
                                        1 => 2001,
                                    ],
                                    'minYear' => 2001,
                                    'maxYear' => 2001,
                                    'period' => 1,
                                    'slope' => 0.123,
                                    'average' => 0.456,
                                    'surveys' => [
                                        1 => 'MICS01',
                                    ],
                                ];
                            } elseif ($id == 2) {
                                return [
                                    'values' => [
                                        2 => 0.2,
                                        3 => 0.7,
                                    ],
                                    'count' => 2,
                                    'years' => [
                                        2 => 2001,
                                        3 => 1991,
                                    ],
                                    'minYear' => 1991,
                                    'maxYear' => 2001,
                                    'period' => 10,
                                    'slope' => 0.123,
                                    'average' => 0.456,
                                    'surveys' => [
                                        2 => 'MICS01',
                                        3 => 'JMP91',
                                    ],
                                ];
                            } else {
                                return null;
                            }
                        })
        );

        $stubCalculator->expects($this->any())
                ->method('computeFlattenAllYears')
                ->will($this->returnCallback(function(\Application\Model\Filter $filter, array $questionnaires, Part $part) {
                            $id = count($questionnaires) ? $questionnaires[0]->getId() : null;
                            if ($id == null) {
                                return [
                                    null,
                                    null,
                                    null,
                                ];
                            } elseif ($id == 1) {
                                return [
                                    0.25,
                                    0.90,
                                    null,
                                ];
                            } elseif ($id == 2) {
                                return [
                                    0.15,
                                    0.80,
                                    0.90,
                                ];
                            } else {
                                return null;
                            }
                        }));

        $stubCalculator->setQuestionnaireRepository($this->getStubQuestionnaireRepository());
        $stubCalculator->setPopulationRepository($this->getStubPopulationRepository());

        return $stubCalculator;
    }

    public function testComputeFilterForAllQuestionnaires()
    {
        $expectedSingle = [
            'values' => [
                1 => 0.5,
            ],
            'years' => [
                1 => 2001,
            ],
            'surveys' => [
                1 => 'MICS01',
            ],
        ];

        $aggregator = new \Application\Service\Calculator\Aggregator();
        $calculator = $this->getStubCalculator();
        $aggregator->setCalculator($calculator);
        $aggregated = $aggregator->computeFilterForAllQuestionnaires(123, $this->geoname, 456);
        $this->assertEquals($expectedSingle, $aggregated, 'geoname without hierarchy should return only his values');

        $expectedWithChild = [
            'values' => [
                1 => 0.5,
                2 => 0.2,
                3 => 0.7,
            ],
            'years' => [
                1 => 2001,
                2 => 2001,
                3 => 1991,
            ],
            'surveys' => [
                1 => 'MICS01',
                2 => 'MICS01',
                3 => 'JMP91',
            ],
        ];

        // Add a children geoname with two more questionnaires
        $child = $this->getNewModelWithId('\Application\Model\Geoname');
        $this->geoname->getChildren()->add($child);
        $questionnaire2 = $this->getNewModelWithId('\Application\Model\Questionnaire');
        $questionnaire2->setGeoname($child);
        $questionnaire3 = $this->getNewModelWithId('\Application\Model\Questionnaire');
        $questionnaire3->setGeoname($child);

        // The same call as before should yield different result, because we now have a child
        $aggregatedWithChild = $aggregator->computeFilterForAllQuestionnaires(123, $this->geoname, 456);
        $this->assertEquals($expectedWithChild, $aggregatedWithChild, 'geoname with children should return all of his  and his children\'s values');
    }

    public function testComputeFlattenAllYears()
    {
        $aggregator = new \Application\Service\Calculator\Aggregator();
        $calculator = $this->getStubCalculator();
        $aggregator->setCalculator($calculator);

        $notAggregated = $calculator->computeFlattenAllYears($this->filter1, [$this->questionnaire], $this->part1);
        $aggregated = $aggregator->computeFlattenAllYears([$this->filter1], $this->geoname, $this->part1);
        $this->assertEquals($notAggregated, $aggregated[0]['data'], 'geoname without hierarchy should be exactly the same as non-aggregated');

        $expectedWithChild = [
            [
                'name' => 'Filter 1',
                'id' => 1,
                'data' => [
                    0.175,
                    0.825,
                    0.90,
                ]
            ]
        ];

        // Second geoname has two more questionnaire
        $questionnaire2 = $this->getNewModelWithId('\Application\Model\Questionnaire');
        $questionnaire2->setGeoname($this->geoname2);
        $questionnaire3 = $this->getNewModelWithId('\Application\Model\Questionnaire');
        $questionnaire3->setGeoname($this->geoname2);

        // Both geonames are the children of a common parent
        $parent = $this->getNewModelWithId('\Application\Model\Geoname');
        $parent->getChildren()->add($this->geoname);
        $parent->getChildren()->add($this->geoname2);

        // The same call as before should yield different result, because we now have a child
        $aggregatedWithChild = $aggregator->computeFlattenAllYears([$this->filter1], $parent, $this->part1);
        $this->assertEquals($expectedWithChild, $aggregatedWithChild, 'geoname with children should return all of his  and his children\'s values');
    }

}
