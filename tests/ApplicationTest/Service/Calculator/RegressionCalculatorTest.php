<?php

namespace ApplicationTest\Service\Calculator;

/**
 * @group Calculator
 */
class RegressionCalculatorTest extends AbstractCalculator
{

    /**
     * @var \Application\Model\FilterSet
     */
    private $filterSet;

    /**
     * @var \Application\Service\Calculator\Calculator
     */
    private $service;

    /**
     * @var \Application\Service\Calculator\Calculator
     */
    private $service2;

    public function setUp()
    {
        parent::setUp();

        // Define a second questionnaire with answers for leaf filters only
        // Create a stub for the Questionnaire class with fake ID, so we don't have to mess with database
        $questionnaire2 = $this->getNewModelWithId('\Application\Model\Questionnaire');

        $survey2 = new \Application\Model\Survey('Test survey 2');
        $survey2->setCode('tst 2')->setYear(2005);
        $questionnaire2->setSurvey($survey2)->setGeoname($this->geoname);

        $question131 = new \Application\Model\Question\NumericQuestion();
        $question32 = new \Application\Model\Question\NumericQuestion();

        $question131->setFilter($this->filter131);
        $question32->setFilter($this->filter32);

        $answer131 = new \Application\Model\Answer();
        $answer32 = new \Application\Model\Answer();

        $answer131->setPart($this->part1)->setQuestionnaire($questionnaire2)->setQuestion($question131)->setValuePercent(0.1);
        $answer32->setPart($this->part1)->setQuestionnaire($questionnaire2)->setQuestion($question32)->setValuePercent(0.000001);

        $this->questionnaires = [$this->questionnaire, $questionnaire2];

        $this->filterSet = new \Application\Model\FilterSet('water');
        $this->filterSet->addFilter($this->highFilter1)
                ->addFilter($this->highFilter2)
                ->addFilter($this->highFilter3);

        $this->service = $this->getNewCalculator();
        $this->service2 = $this->getNewCalculator();
    }

    public function testStubPopulation()
    {
        $stubPopulationRepository = $this->getStubPopulationRepository();
        $this->assertEquals(10, $stubPopulationRepository->getPopulationByGeoname($this->geoname, $this->part1->getId(), 2000));
        $this->assertEquals(3, $stubPopulationRepository->getPopulationByGeoname($this->geoname, $this->part2->getId(), 2000));
        $this->assertEquals(7, $stubPopulationRepository->getPopulationByGeoname($this->geoname, $this->partTotal->getId(), 2000));

        $this->assertEquals(15, $stubPopulationRepository->getPopulationByGeoname($this->geoname, $this->part1->getId(), 2005));
        $this->assertEquals(3, $stubPopulationRepository->getPopulationByGeoname($this->geoname, $this->part2->getId(), 2005));
        $this->assertEquals(12, $stubPopulationRepository->getPopulationByGeoname($this->geoname, $this->partTotal->getId(), 2005));
    }

    public function testComputeFilterForAllQuestionnaires()
    {
        $this->assertEquals([
            'values' => [
                1 => 0.1111,
                2 => 0.1,
            ],
            'count' => 2,
            'years' => [
                1 => 2000,
                2 => 2005,
            ],
            'minYear' => 2000,
            'maxYear' => 2005,
            'period' => 5,
            'slope' => -0.00222,
            'average' => 0.10555,
            'surveys' => [
                1 => [
                    'code' => 'tst 1',
                    'name' => 'Test survey 1',
                    ],
                2 => [
                    'code' => 'tst 2',
                    'name' => 'Test survey 2',
                ],
            ],
                ], $this->service->computeFilterForAllQuestionnaires($this->highFilter1->getId(), $this->questionnaires, $this->part1->getId()));

        $this->assertEquals([
            'values' => [],
            'count' => 0,
            'years' => [],
            'minYear' => null,
            'maxYear' => null,
            'period' => 1,
            'slope' => null,
            'average' => null,
            'surveys' => [],
                ], $this->service->computeFilterForAllQuestionnaires($this->highFilter1->getId(), [], $this->part1->getId()), 'no questionnaires should still return valid structure');
    }

    /**
     * Return input and output data for regression computation
     * @return array
     */
    public function regressionProvider()
    {
        return [
            [2000, 0.1111],
            [2001, 0.10888],
            [2002, 0.10666],
            [2003, 0.10444],
            [2004, 0.10222],
            [2005, 0.1],
        ];
    }

    /**
     * @dataProvider regressionProvider
     */
    public function testComputeRegressionForUnknownYears($year, $expected)
    {
        $this->assertEquals($expected, $this->service->computeRegressionOneYear($year, $this->highFilter1->getId(), $this->questionnaires, $this->part1->getId()), 'regression between known years according');
        $this->assertNull($this->service->computeRegressionOneYear($year, $this->highFilter1->getId(), [], $this->part1->getId()), 'no questionnaires should still return valid structure');
    }

    public function testComputeRegressionForShortPeriod()
    {
        $this->questionnaire->getSurvey()->setYear(2003);
        $this->assertEquals(0.105556, $this->service->computeRegressionOneYear(2004, $this->highFilter3->getId(), $this->questionnaires, $this->part1->getId()), 'regression between known years according');
    }

    public function computeFlattenOneYearProvider()
    {
        return [
            // Basic casses
            [[2000 => 0, 2001 => 1, 2002 => 0.5, 1950 => null], [
                    'all' => [
                        2000 => -10,
                        2001 => 10,
                        2002 => 0.5,
                    ],
                    'min' => -10,
                    'max' => 10,
                ]],
            // Cases with undefined values, based on the year earlier
            [[2001 => null, 2004 => 0.04, 2006 => 0.96], [
                    'all' => [
                        2000 => 0.5,
                        2001 => null,
                        2002 => 0.5,
                        2003 => 0.04,
                        2004 => null,
                        2005 => 0.96,
                        2006 => null,
                    ],
                    'min' => 0.04,
                    'max' => 0.96,
                ]],
            [[2002 => 0.04], [
                    'all' => [
                        2000 => 0.01,
                        2001 => 0.04,
                        2002 => null,
                    ],
                    'min' => 0.01,
                    'max' => 0.04,
                ]],
            [[2002 => 0.96], [
                    'all' => [
                        2000 => 0.99,
                        2001 => 0.96,
                        2002 => null,
                    ],
                    'min' => 0.96,
                    'max' => 0.99,
                ]],
            [[2001 => 1], [
                    'all' => [
                        2000 => 1,
                        2001 => null,
                    ],
                    'min' => 1,
                    'max' => 1,
                ]],
            [[2001 => 0], [
                    'all' => [
                        2000 => 0,
                        2001 => null,
                    ],
                    'min' => 0,
                    'max' => 0,
                ]],
            // Cases with undefined values, based on the year later
            [[2000 => null, 2002 => 0.04, 2005 => 0.96], [
                    'all' => [
                        2000 => null,
                        2001 => 0.5,
                        2002 => null,
                        2003 => 0.04,
                        // NO 2004 !
                        2005 => null,
                        2006 => 0.96,
                    ],
                    'min' => 0.04,
                    'max' => 0.96,
                ]],
            [[2000 => 0.04], [
                    'all' => [
                        2000 => null,
                        2001 => 0.04,
                        2002 => 0.01,
                    ],
                    'min' => 0.01,
                    'max' => 0.04,
                ]],
            [[2000 => 0.96], [
                    'all' => [
                        2000 => null,
                        2001 => 0.96,
                        2002 => 0.99,
                    ],
                    'min' => 0.96,
                    'max' => 0.99,
                ]],
            [[2000 => 1], [
                    'all' => [
                        2000 => null,
                        2001 => 1,
                    ],
                    'min' => 1,
                    'max' => 1,
                ]],
            [[2000 => 0], [
                    'all' => [
                        2000 => null,
                        2001 => 0,
                    ],
                    'min' => 0,
                    'max' => 0,
                ]],
        ];
    }

    /**
     * @dataProvider computeFlattenOneYearProvider
     */
    public function testComputeFlattenOneYear($params, $allRegressions)
    {
        foreach ($params as $year => $expected) {
            $this->assertEquals($expected, $this->service->computeFlattenOneYear($year, $allRegressions));
        }
    }

    public function flattenProvider()
    {
        return [
            [true, [
                    0 => [
                        26 => 0.09778,
                        27 => 0.095560000000001,
                    ],
                    1 => [
                        26 => null,
                        27 => null,
                    ],
                    2 => [
                        26 => 0.097779,
                        27 => 0.095557,
                    ],
                ]],
            // No questionnaires at all, to assert that structure returned is still valid
            [false, [
                    0 => [
                        26 => null,
                        27 => null,
                    ],
                    1 => [
                        26 => null,
                        27 => null,
                    ],
                    2 => [
                        26 => null,
                        27 => null,
                    ],
                ]],
            [true, [
                    0 => [
                        10 => null,
                        11 => null,
                        12 => null,
                        13 => null,
                        14 => 0.11554,
                        15 => 0.11554,
                        16 => 0.11554,
                        17 => 0.11554,
                        18 => 0.11554,
                        19 => 0.11332,
                        20 => 0.1111,
                        21 => 0.10888,
                        22 => 0.10666,
                        23 => 0.10444,
                        24 => 0.10222,
                        25 => 0.1,
                        26 => 0.09778,
                        27 => 0.095560000000001,
                        28 => 0.095560000000001,
                        29 => 0.095560000000001,
                        30 => 0.095560000000001,
                        31 => 0.095560000000001,
                        32 => null,
                        33 => null,
                        34 => null,
                        35 => null,
                    ],
                    1 => [
                        10 => null,
                        11 => null,
                        12 => null,
                        13 => null,
                        14 => null,
                        15 => null,
                        16 => null,
                        17 => null,
                        18 => null,
                        19 => null,
                        20 => null,
                        21 => null,
                        22 => null,
                        23 => null,
                        24 => null,
                        25 => null,
                        26 => null,
                        27 => null,
                        28 => null,
                        29 => null,
                        30 => null,
                        31 => null,
                        32 => null,
                        33 => null,
                        34 => null,
                        35 => null,
                    ],
                    2 => [
                        10 => null,
                        11 => null,
                        12 => null,
                        13 => null,
                        14 => 0.115555,
                        15 => 0.115555,
                        16 => 0.115555,
                        17 => 0.115555,
                        18 => 0.115555,
                        19 => 0.113333,
                        20 => 0.111111,
                        21 => 0.108889,
                        22 => 0.106667,
                        23 => 0.104445,
                        24 => 0.102223,
                        25 => 0.100001,
                        26 => 0.097779,
                        27 => 0.095557,
                        28 => 0.095557,
                        29 => 0.095557,
                        30 => 0.095557,
                        31 => 0.095557,
                        32 => null,
                        33 => null,
                        34 => null,
                        35 => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider flattenProvider
     */
    public function testComputeFlatten($useQuestionnaires, $partialExpecteds)
    {
        foreach ($this->filterSet->getFilters() as $i => $filter) {
            $actual = $this->service->computeFlattenAllYears($filter, $useQuestionnaires ? $this->questionnaires : [], $this->part1);
            $partialExpected = $partialExpecteds[$i];
            $partialActual = $this->removeExtraData($partialExpected, $actual);

            $this->assertEquals($partialExpected, $partialActual, 'the partial data expected must match');
        }
    }

    /**
     * Because we check only partial data, remove extraneous things from actual result
     * @param array $partialExpected
     * @param array $actual
     * @return array
     */
    private function removeExtraData(array $partialExpected, array $actual)
    {
        $result = array_intersect_key($actual, $partialExpected);

        return $result;
    }

    public function testCacheOnFilterForAllQuestionnaire()
    {
        $res1 = [];
        foreach ($this->filterSet->getFilters() as $i => $filter) {
            $res1[] = $this->service->computeFlattenAllYears($filter, $this->questionnaires, $this->part1);
        }

        $this->answer131->setValuePercent(0.2);
        $res2 = [];
        $res3 = [];
        foreach ($this->filterSet->getFilters() as $i => $filter) {
            $res2[] = $this->service->computeFlattenAllYears($filter, $this->questionnaires, $this->part1);
            $res3[] = $this->service2->computeFlattenAllYears($filter, $this->questionnaires, $this->part1);
        }
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');
        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');

        $partialExpected = [
            0 => [
                26 => 0.077779999999997,
                27 => 0.05556,
            ],
            1 => [
                26 => null,
                27 => null,
            ],
            2 => [
                26 => 0.077778999999992,
                27 => 0.055556999999993,
            ],
        ];

        foreach ($res3 as $i => $actual) {
            $partialActual = $this->removeExtraData($partialExpected[$i], $actual);
            $this->assertEquals($partialExpected[$i], $partialActual, 'after clearing cache, result reflect new values');
        }
    }

    public function testRealRepositoriesCanBeFound()
    {
        $service = new \Application\Service\Calculator\Calculator();
        $service->setServiceLocator($this->getApplicationServiceLocator());

        $this->assertInstanceOf('Application\Repository\PopulationRepository', $service->getPopulationRepository());
        $this->assertInstanceOf('Application\Repository\PartRepository', $service->getPartRepository());
    }

    public function testExcludeRules()
    {
        $exclude = new \Application\Model\Rule\Rule();
        $exclude->setFormula('=NULL');

        $filterQuestionnaireUsage = new \Application\Model\Rule\FilterQuestionnaireUsage();
        $filterQuestionnaireUsage->setPart($this->part1)->setQuestionnaire($this->questionnaire)->setRule($exclude)->setFilter($this->filter1);

        $this->assertEquals([
            'values' => [
                1 => null,
                2 => 0.1,
            ],
            'count' => 1,
            'years' => [
                1 => 2000,
                2 => 2005,
            ],
            'minYear' => 2005,
            'maxYear' => 2005,
            'period' => 1,
            'slope' => null,
            'average' => 0.1,
            'surveys' => [
                1 => [
                    'code' => 'tst 1',
                    'name' => 'Test survey 1',
                ],
                2 => [
                    'code' => 'tst 2',
                    'name' => 'Test survey 2',
                ],
            ],
                ], $this->service->computeFilterForAllQuestionnaires($this->filter1->getId(), $this->questionnaires, $this->part1->getId()));
    }

    public function testAllZeroValueShouldNotDivideByZero()
    {
        // Set everything to zero
        foreach ($this->questionnaires as $questionnaire) {
            foreach ($questionnaire->getAnswers() as $answer) {
                $answer->setValuePercent(0);
            }
        }

        // This call should NOT raise PHP warnings
        $this->assertEquals(0, $this->service->computeRegressionOneYear(2006, $this->filter1->getId(), $this->questionnaires, $this->part1->getId()));
    }

    public function testAllIdenticalValueShouldNotDivideByZero()
    {
        // Set everything to to have the same value for both questionnaire
        foreach ($this->questionnaires[0]->getAnswers() as $answer) {
            $answer->setValuePercent(1);
        }
        foreach ($this->questionnaires[1]->getAnswers() as $answer) {
            $answer->setValuePercent(4);
        }

        // This call should NOT raise PHP warnings
        $this->assertEquals(4, $this->service->computeRegressionOneYear(2006, $this->filter1->getId(), $this->questionnaires, $this->part1->getId()));
    }

    /**
     * @dataProvider computeFormulaAfterRegressionProvider
     */
    public function testcomputeFormulaAfterRegression($formula, $configurator)
    {
        $rule = new \Application\Model\Rule\Rule();
        $rule->setFormula($formula);
        $year = 2000;
        $currentFilterId = 1;
        $questionnaires = $this->questionnaires;
        $partId = 4;

        // Test that we the custom syntax is correctly interpreted and submethods are correctly called
        $mockedCalculator = $this->getMock('\Application\Service\Calculator\Calculator', ['computeFlattenOneYearWithFormula', 'computeFilterForAllQuestionnaires']);
        $mockedCalculator->setServiceLocator($this->getApplicationServiceLocator());
        $configurator($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId);

        $mockedCalculator->computeFormulaAfterRegression($rule, $year, $currentFilterId, $questionnaires, $partId);
    }

    public function testcomputeFormulaAfterRegressionReturnsYear()
    {
        $rule = new \Application\Model\Rule\Rule();
        $rule->setFormula('={Y}');
        $year = 2000;
        $currentFilterId = 1;
        $questionnaires = $this->questionnaires;
        $partId = 4;

        $result = $this->service->computeFormulaAfterRegression($rule, $year, $currentFilterId, $questionnaires, $partId);

        $this->assertEquals($year, $result, 'should return the current year');
    }

    /**
     * Return formula and configurator for mockCalculator
     * @return array
     */
    public function computeFormulaAfterRegressionProvider()
    {
        return [
            ['={F#345,P#current,Y0}', function ($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year), $this->equalTo(345), $this->equalTo($questionnaires), $this->equalTo($partId));
                }],
            ['={F#345,P#current,Y+2}', function ($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year + 2), $this->equalTo(345), $this->equalTo($questionnaires), $this->equalTo($partId));
                }],
            ['={F#345,P#678,Y-1}', function ($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year - 1), $this->equalTo(345), $this->equalTo($questionnaires), $this->equalTo(678));
                }],
            ['={F#current,P#current,Y0}', function ($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year), $this->equalTo($currentFilterId), $this->equalTo($questionnaires), $this->equalTo($partId));
                }],
            ['={self}', function ($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year), $this->equalTo($currentFilterId), $this->equalTo($questionnaires), $this->equalTo($partId));
                }],
            ['={F#12,Q#all}', function ($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFilterForAllQuestionnaires')
                            ->with($this->equalTo(12), $this->equalTo($questionnaires), $this->equalTo($partId))
                            ->will($this->returnValue(['values' => [1]]));
                }],
            ['={F#current,Q#all}', function ($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFilterForAllQuestionnaires')
                            ->with($this->equalTo($currentFilterId), $this->equalTo($questionnaires), $this->equalTo($partId))
                            ->will($this->returnValue(['values' => [1]]));
                }],
            ['={Q#all,P#2}', function ($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {

                    $mockedPop = $this->getMock('\Application\Repository\PopulationRepository', ['getPopulationByGeoname'], [], '', false);

                    $mockedCalculator->setPopulationRepository($mockedPop);

                    foreach ($questionnaires as $q) {
                        $mockedPop->expects($this->exactly(count($questionnaires)))
                                ->method('getPopulationByGeoname')
                                ->with($this->anything(), $this->equalTo(2), $year)
                                ->will($this->returnValue(null));
                    }
                }],
        ];
    }

}
