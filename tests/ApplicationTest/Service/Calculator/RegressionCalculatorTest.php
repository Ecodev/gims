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

        $this->questionnaires = array($this->questionnaire, $questionnaire2);

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
        $this->assertEquals(array(
            'values' =>
            array(
                1 => 0.1111,
                2 => 0.1,
            ),
            'count' => 2,
            'years' =>
            array(
                1 => 2000,
                2 => 2005,
            ),
            'minYear' => 2000,
            'maxYear' => 2005,
            'period' => 5,
            'slope' => -0.00222,
            'average' => 0.10555,
            'surveys' => array(
                1 => 'tst 1',
                2 => 'tst 2',
            ),
                ), $this->service->computeFilterForAllQuestionnaires($this->highFilter1->getId(), $this->questionnaires, $this->part1->getId()));

        $this->assertEquals(array(
            'values' => array(),
            'count' => 0,
            'years' => array(),
            'minYear' => null,
            'maxYear' => null,
            'period' => 1,
            'slope' => null,
            'average' => null,
            'surveys' => array(),
                ), $this->service->computeFilterForAllQuestionnaires($this->highFilter1->getId(), array(), $this->part1->getId()), 'no questionnaires should still return valid structure');
    }

    /**
     * Return input and output data for regression computation
     * @return array
     */
    public function regressionProvider()
    {
        return array(
            array(2000, 0.1111),
            array(2001, 0.10888),
            array(2002, 0.10666),
            array(2003, 0.10444),
            array(2004, 0.10222),
            array(2005, 0.1),
        );
    }

    /**
     * @dataProvider regressionProvider
     */
    public function testComputeRegressionForUnknownYears($year, $expected)
    {
        $this->assertEquals($expected, $this->service->computeRegressionOneYear($year, $this->highFilter1->getId(), $this->questionnaires, $this->part1->getId()), 'regression between known years according');
        $this->assertNull($this->service->computeRegressionOneYear($year, $this->highFilter1->getId(), array(), $this->part1->getId()), 'no questionnaires should still return valid structure');
    }

    public function testComputeRegressionForShortPeriod()
    {
        $this->questionnaire->getSurvey()->setYear(2003);
        $this->assertEquals(0.105556, $this->service->computeRegressionOneYear(2004, $this->highFilter3->getId(), $this->questionnaires, $this->part1->getId()), 'regression between known years according');
    }

    public function computeFlattenOneYearProvider()
    {
        return array(
            // Basic casses
            array(array(2000 => 0, 2001 => 1, 2002 => 0.5, 1950 => null), array(
                    2000 => -10,
                    2001 => 10,
                    2002 => 0.5,
                )),
            // Cases with undefined values, based on the year earlier
            array(array(2001 => null, 2004 => 0.04, 2006 => 0.96), array(
                    2000 => 0.5,
                    2001 => null,
                    2002 => 0.5,
                    2003 => 0.04,
                    2004 => null,
                    2005 => 0.96,
                    2006 => null,
                )),
            array(array(2002 => 0.04), array(
                    2000 => 0.01,
                    2001 => 0.04,
                    2002 => null,
                )),
            array(array(2002 => 0.96), array(
                    2000 => 0.99,
                    2001 => 0.96,
                    2002 => null,
                )),
            array(array(2001 => 1), array(
                    2000 => 1,
                    2001 => null,
                )),
            array(array(2001 => 0), array(
                    2000 => 0,
                    2001 => null,
                )),
            // Cases with undefined values, based on the year later
            array(array(2000 => null, 2002 => 0.04, 2005 => 0.96), array(
                    2000 => null,
                    2001 => 0.5,
                    2002 => null,
                    2003 => 0.04,
                    // NO 2004 !
                    2005 => null,
                    2006 => 0.96,
                )),
            array(array(2000 => 0.04), array(
                    2000 => null,
                    2001 => 0.04,
                    2002 => 0.01,
                )),
            array(array(2000 => 0.96), array(
                    2000 => null,
                    2001 => 0.96,
                    2002 => 0.99,
                )),
            array(array(2000 => 1), array(
                    2000 => null,
                    2001 => 1,
                )),
            array(array(2000 => 0), array(
                    2000 => null,
                    2001 => 0,
                )),
        );
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
        return array(
            array(true, array(
                    0 => array(
                        'name' => 'improved',
                        'data' => array(
                            26 => 0.09778,
                            27 => 0.095560000000001,
                        ),
                        'id' => 15
                    ),
                    1 => array(
                        'name' => 'unimproved',
                        'data' =>
                        array(
                            26 => NULL,
                            27 => NULL,
                        ),
                        'id' => 16
                    ),
                    2 => array(
                        'name' => 'total',
                        'data' =>
                        array(
                            26 => 0.097779,
                            27 => 0.095557,
                        ),
                        'id' => 17
                    ),
                )),
            // No questionnaires at all, to assert that structure returned is still valid
            array(false, array(
                    0 => array(
                        'name' => 'improved',
                        'data' => array(
                            26 => NULL,
                            27 => NULL,
                        ),
                        'id' => 15
                    ),
                    1 => array(
                        'name' => 'unimproved',
                        'data' => array(
                            26 => NULL,
                            27 => NULL,
                        ),
                        'id' => 16
                    ),
                    2 => array(
                        'name' => 'total',
                        'data' => array(
                            26 => NULL,
                            27 => NULL,
                        ),
                        'id' => 17
                    ),
                )),
            array(true, array(
                    0 => array(
                        'name' => 'improved',
                        'data' => array(
                            10 => NULL,
                            11 => NULL,
                            12 => NULL,
                            13 => NULL,
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
                        ),
                        'id' => 15
                    ),
                    1 => array(
                        'name' => 'unimproved',
                        'data' => array(
                            10 => NULL,
                            11 => NULL,
                            12 => NULL,
                            13 => NULL,
                            14 => NULL,
                            15 => NULL,
                            16 => NULL,
                            17 => NULL,
                            18 => NULL,
                            19 => NULL,
                            20 => NULL,
                            21 => NULL,
                            22 => NULL,
                            23 => NULL,
                            24 => NULL,
                            25 => NULL,
                            26 => NULL,
                            27 => NULL,
                            28 => NULL,
                            29 => NULL,
                            30 => NULL,
                            31 => NULL,
                            32 => NULL,
                            33 => NULL,
                            34 => NULL,
                            35 => NULL,
                        ),
                        'id' => 16
                    ),
                    2 => array(
                        'name' => 'total',
                        'data' => array(
                            10 => NULL,
                            11 => NULL,
                            12 => NULL,
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
                        ),
                        'id' => 17
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider flattenProvider
     */
    public function testComputeFlatten($useQuestionnaires, $partialExpected)
    {
        $actual = $this->service->computeFlattenAllYears($this->filterSet->getFilters(), $useQuestionnaires ? $this->questionnaires : array(), $this->part1);

        $partialActual = $this->removeExtraData($partialExpected, $actual);

        $this->assertEquals($partialExpected, $partialActual, 'the partial data expected must match');
    }

    /**
     * Because we check only partial data, remove extraneous things from actual result
     * @param array $partialExpected
     * @param array $actual
     * @return array
     */
    private function removeExtraData(array $partialExpected, array $actual)
    {
        $this->assertEquals(array_keys($partialExpected), array_keys($actual), 'we should get all filters asked');
        foreach ($actual as $i => &$d) {
            $d['data'] = array_intersect_key($d['data'], $partialExpected[$i]['data']);
        }

        return $actual;
    }

    public function testCacheOnFilterForAllQuestionnaire()
    {
        $res1 = $this->service->computeFlattenAllYears($this->filterSet->getFilters(), $this->questionnaires, $this->part1);

        $this->answer131->setValuePercent((0.2));
        $res2 = $this->service->computeFlattenAllYears($this->filterSet->getFilters(), $this->questionnaires, $this->part1);
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');

        $res3 = $this->service2->computeFlattenAllYears($this->filterSet->getFilters(), $this->questionnaires, $this->part1);
        $partialExpected = array(
            0 => array(
                'name' => 'improved',
                'data' =>
                array(
                    26 => 0.077779999999997,
                    27 => 0.05556,
                ),
                'id' => 15
            ),
            1 => array(
                'name' => 'unimproved',
                'data' =>
                array(
                    26 => NULL,
                    27 => NULL,
                ),
                'id' => 16
            ),
            2 => array(
                'name' => 'total',
                'data' =>
                array(
                    26 => 0.077778999999992,
                    27 => 0.055556999999993,
                ),
                'id' => 17
            ),
        );

        $partialRes3 = $this->removeExtraData($partialExpected, $res3);
        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals($partialExpected, $partialRes3, 'after clearing cache, result reflect new values');
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

        $this->assertEquals(array(
            'values' =>
            array(
                1 => null,
                2 => 0.1,
            ),
            'count' => 1,
            'years' =>
            array(
                1 => 2000,
                2 => 2005,
            ),
            'minYear' => 2005,
            'maxYear' => 2005,
            'period' => 1,
            'slope' => null,
            'average' => 0.1,
            'surveys' => array(
                1 => 'tst 1',
                2 => 'tst 2',
            ),
                ), $this->service->computeFilterForAllQuestionnaires($this->filter1->getId(), $this->questionnaires, $this->part1->getId()));
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
        $mockedCalculator = $this->getMock('\Application\Service\Calculator\Calculator', array('computeFlattenOneYearWithFormula', 'computeFilterForAllQuestionnaires'));
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
        return array(
            array('={F#345,P#current,Y0}', function($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year), $this->equalTo(345), $this->equalTo($questionnaires), $this->equalTo($partId));
                }),
            array('={F#345,P#current,Y+2}', function($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year + 2), $this->equalTo(345), $this->equalTo($questionnaires), $this->equalTo($partId));
                }),
            array('={F#345,P#678,Y-1}', function($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year - 1), $this->equalTo(345), $this->equalTo($questionnaires), $this->equalTo(678));
                }),
            array('={F#current,P#current,Y0}', function($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year), $this->equalTo($currentFilterId), $this->equalTo($questionnaires), $this->equalTo($partId));
                }),
            array('={self}', function($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFlattenOneYearWithFormula')
                            ->with($this->equalTo($year), $this->equalTo($currentFilterId), $this->equalTo($questionnaires), $this->equalTo($partId));
                }),
            array('={F#12,Q#all}', function($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFilterForAllQuestionnaires')
                            ->with($this->equalTo(12), $this->equalTo($questionnaires), $this->equalTo($partId))
                            ->will($this->returnValue(array('values' => array(1))));
                }),
            array('={F#current,Q#all}', function($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {
                    $mockedCalculator->expects($this->once())
                            ->method('computeFilterForAllQuestionnaires')
                            ->with($this->equalTo($currentFilterId), $this->equalTo($questionnaires), $this->equalTo($partId))
                            ->will($this->returnValue(array('values' => array(1))));
                }),
            array('={Q#all,P#2}', function($mockedCalculator, $year, $currentFilterId, $questionnaires, $partId) {

                    $mockedPop = $this->getMock('\Application\Repository\PopulationRepository', array('getPopulationByGeoname'), array(), '', false);

                    $mockedCalculator->setPopulationRepository($mockedPop);

                    foreach ($questionnaires as $q) {
                        $mockedPop->expects($this->exactly(count($questionnaires)))
                                ->method('getPopulationByGeoname')
                                ->with($this->anything(), $this->equalTo(2), $year)
                                ->will($this->returnValue(null));
                    }
                }),
            );
    }

}
