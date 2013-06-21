<?php

namespace ApplicationTest\Service\Calculator;

class JmpTest extends AbstractCalculator
{

    /**
     * @var \Application\Model\Part
     */
    private $part1;

    /**
     * @var \Application\Model\Part
     */
    private $part2;

    /**
     * @var \Application\Model\FilterSet
     */
    private $filterSet;

    /**
     * @var \Application\Service\Calculator\Jmp
     */
    private $service;

    /**
     * @var \Application\Service\Calculator\Jmp
     */
    private $service2;

    public function setUp()
    {
        parent::setUp();

        $this->questionnaire2 = new \Application\Model\Questionnaire();


        // Define a second questionnaire with answers for leaf filters only
        $questionnaire2 = new \Application\Model\Questionnaire();

        $survey2 = new \Application\Model\Survey();
        $survey2->setCode('tst 2')->setName('Test survey 2')->setYear(2005);
        $questionnaire2->setSurvey($survey2);

        $question131 = new \Application\Model\Question();
        $question32 = new \Application\Model\Question();

        $question131->setFilter($this->filter131);
        $question32->setFilter($this->filter32);

        $answer131 = new \Application\Model\Answer();
        $answer32 = new \Application\Model\Answer();

        $answer131->setQuestionnaire($questionnaire2)->setQuestion($question131)->setValueAbsolute(0.1);
        $answer32->setQuestionnaire($questionnaire2)->setQuestion($question32)->setValueAbsolute(0.000001);


        $this->questionnaires = array($this->questionnaire, $questionnaire2);

        $this->filterSet = new \Application\Model\FilterSet('water');
        $this->filterSet->addFilter($this->highFilter1)
                ->addFilter($this->highFilter2)
                ->addFilter($this->highFilter3);

        $this->part1 = new \Application\Model\Part('tst part 1');
        $this->part2 = new \Application\Model\Part('tst part 2');

        // Create a stub for the PartRepository class, so we don't have to mess with database
        $stubPartRepository = $this->getMock('\Application\Repository\PartRepository', array('findAll'), array(), '', false);
        $stubPartRepository->expects($this->any())
                ->method('findAll')
                ->will($this->returnValue(array($this->part1, $this->part2)));

        // Create a stub for the PopulationRepository class with predetermined values, so we don't have to mess with database
        $stubPopulationRepository = $this->getMock('\Application\Repository\PopulationRepository', array('getOneByQuestionnaire'), array(), '', false);
        $stubPopulationRepository->expects($this->any())
                ->method('getOneByQuestionnaire')
                ->will($this->returnValueMap(array(
                            array($this->questionnaire, null, (new \Application\Model\Population())
                                ->setPopulation(10)
                            ),
                            array($questionnaire2, null, (new \Application\Model\Population())
                                ->setPopulation(15)
                            ),
                            array($this->questionnaire, $this->part1, (new \Application\Model\Population())
                                ->setPopulation(3)
                            ),
                            array($questionnaire2, $this->part1, (new \Application\Model\Population())
                                ->setPopulation(3)
                            ),
                            array($this->questionnaire, $this->part2, (new \Application\Model\Population())
                                ->setPopulation(7)
                            ),
                            array($questionnaire2, $this->part2, (new \Application\Model\Population())
                                ->setPopulation(12)
                            ),
        )));


        $this->assertEquals(10, $stubPopulationRepository->getOneByQuestionnaire($this->questionnaire, null)->getPopulation());
        $this->assertEquals(3, $stubPopulationRepository->getOneByQuestionnaire($this->questionnaire, $this->part1)->getPopulation());
        $this->assertEquals(7, $stubPopulationRepository->getOneByQuestionnaire($this->questionnaire, $this->part2)->getPopulation());

        $this->assertEquals(15, $stubPopulationRepository->getOneByQuestionnaire($questionnaire2, null)->getPopulation());
        $this->assertEquals(3, $stubPopulationRepository->getOneByQuestionnaire($questionnaire2, $this->part1)->getPopulation());
        $this->assertEquals(12, $stubPopulationRepository->getOneByQuestionnaire($questionnaire2, $this->part2)->getPopulation());

        $this->service = new \Application\Service\Calculator\Jmp();
        $this->service->setPopulationRepository($stubPopulationRepository);
        $this->service->setPartRepository($stubPartRepository);
        $this->service2 = clone $this->service;
    }

    public function testComputeFilterForAllQuestionnaires()
    {
        $this->assertEquals(array(
            'values' =>
            array(
                'tst 1' => 0.1111,
                'tst 2' => 0.1,
            ),
            'values%' =>
            array(
                'tst 1' => 0.01111,
                'tst 2' => 0.0066666666666667,
            ),
            'count' => 2,
            'years' =>
            array(
                0 => 2000,
                1 => 2005,
            ),
            'minYear' => 2000,
            'maxYear' => 2005,
            'period' => 5,
            'slope' => -0.00222,
            'slope%' => -0.0008886666666666666,
            'average' => 0.10555,
            'average%' => 0.008888333333333333,
            'questionnaire' => array(
                'tst 1' => null,
                'tst 2' => null,
            ),
            'population' => 25,
                ), $this->service->computeFilterForAllQuestionnaires($this->highFilter1, $this->questionnaires));

        $this->assertEquals(array(
            'values' => array(),
            'values%' => array(),
            'count' => 0,
            'years' => array(),
            'minYear' => null,
            'maxYear' => null,
            'period' => 1,
            'slope' => null,
            'slope%' => null,
            'average' => null,
            'average%' => null,
            'population' => 0,
                ), $this->service->computeFilterForAllQuestionnaires($this->highFilter1, array()), 'no questionnaires should still return valid structure');
    }

    /**
     * Return input and output data for regression computation
     * @return array
     */
    public function regressionProvider()
    {
        return array(
            array(2000, array('regression' => 0.01111, 'population' => 25)),
            array(2001, array('regression' => 0.010221333333333, 'population' => 25)),
            array(2002, array('regression' => 0.0093326666666669, 'population' => 25)),
            array(2003, array('regression' => 0.0084440000000001, 'population' => 25)),
            array(2004, array('regression' => 0.0075553333333336, 'population' => 25)),
            array(2005, array('regression' => 0.0066666666666668, 'population' => 25)),
        );
    }

    /**
     * @dataProvider regressionProvider
     */
    public function testComputeRegressionForUnknownYears($year, $expected)
    {
        $this->assertEquals($expected, $this->service->computeRegression($year, $this->highFilter1, $this->questionnaires), 'regression between known years according');
        $this->assertNull($this->service->computeRegression($year, $this->highFilter1, array())['regression'], 'no questionnaires should still return valid structure');
    }

    public function testComputeRegressionForShortPeriod()
    {
        $this->questionnaire->getSurvey()->setYear(2003);
        $this->assertEquals(array('regression' => 0.0088889166666667, 'population' => 25), $this->service->computeRegression(2004, $this->highFilter3, $this->questionnaires), 'regression between known years according');
    }

    public function computeFlattenOneYearProvider()
    {
        return array(
            // Basic casses
            array(array(2000 => 0, 2001 => 1, 2002 => 0.5, 1950 => null), array(
                    2000 => array('regression' => -10),
                    2001 => array('regression' => 10),
                    2002 => array('regression' => 0.5),
                )),
            // Cases with undefined values, based on the year earlier
            array(array(2001 => null, 2004 => 0.04, 2006 => 0.96), array(
                    2000 => array('regression' => 0.5),
                    2001 => array('regression' => null),
                    2002 => array('regression' => 0.5),
                    2003 => array('regression' => 0.04),
                    2004 => array('regression' => null),
                    2005 => array('regression' => 0.96),
                    2006 => array('regression' => null),
                )),
            array(array(2002 => 0.04), array(
                    2000 => array('regression' => 0.01),
                    2001 => array('regression' => 0.04),
                    2002 => array('regression' => null),
                )),
            array(array(2002 => 0.96), array(
                    2000 => array('regression' => 0.99),
                    2001 => array('regression' => 0.96),
                    2002 => array('regression' => null),
                )),
            array(array(2001 => 1), array(
                    2000 => array('regression' => 1),
                    2001 => array('regression' => null),
                )),
            array(array(2001 => 0), array(
                    2000 => array('regression' => 0),
                    2001 => array('regression' => null),
                )),
            // Cases with undefined values, based on the year later
            array(array(2000 => null, 2002 => 0.04, 2005 => 0.96), array(
                    2000 => array('regression' => null),
                    2001 => array('regression' => 0.5),
                    2002 => array('regression' => null),
                    2003 => array('regression' => 0.04),
                    // NO 2004 !
                    2005 => array('regression' => null),
                    2006 => array('regression' => 0.96),
                )),
            array(array(2000 => 0.04), array(
                    2000 => array('regression' => null),
                    2001 => array('regression' => 0.04),
                    2002 => array('regression' => 0.01),
                )),
            array(array(2000 => 0.96), array(
                    2000 => array('regression' => null),
                    2001 => array('regression' => 0.96),
                    2002 => array('regression' => 0.99),
                )),
            array(array(2000 => 1), array(
                    2000 => array('regression' => null),
                    2001 => array('regression' => 1),
                )),
            array(array(2000 => 0), array(
                    2000 => array('regression' => null),
                    2001 => array('regression' => 0),
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
            array(2006, 2007, true, array(
                    0 =>
                    array(
                        'name' => 'improved',
                        'data' =>
                        array(
                            0 => 0.0057780000000001,
                            1 => 0.0048893333333335,
                        ),
                    ),
                    1 =>
                    array(
                        'name' => 'unimproved',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                        ),
                    ),
                    2 =>
                    array(
                        'name' => 'total',
                        'data' =>
                        array(
                            0 => 0.00577786,
                            1 => 0.0048889866666666,
                        ),
                    ),
                )),
            // No questionnaires at all, to assert that structure returned is still valid
            array(2006, 2007, false, array(
                    0 =>
                    array(
                        'name' => 'improved',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                        ),
                    ),
                    1 =>
                    array(
                        'name' => 'unimproved',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                        ),
                    ),
                    2 =>
                    array(
                        'name' => 'total',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                        ),
                    ),
                )),
            array(1990, 2015, true, array(
                    0 =>
                    array(
                        'name' => 'improved',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                            2 => NULL,
                            3 => 0.012887333333333,
                            4 => 0.012887333333333,
                            5 => 0.012887333333333,
                            6 => 0.012887333333333,
                            7 => 0.012887333333333,
                            8 => 0.012887333333333,
                            9 => 0.011998666666667,
                            10 => 0.01111,
                            11 => 0.010221333333333,
                            12 => 0.0093326666666669,
                            13 => 0.0084440000000001,
                            14 => 0.0075553333333336,
                            15 => 0.0066666666666668,
                            16 => 0.0057780000000001,
                            17 => 0.0048893333333335,
                            18 => 0.0048893333333335,
                            19 => 0.0048893333333335,
                            20 => 0.0048893333333335,
                            21 => 0.0048893333333335,
                            22 => 0.0048893333333335,
                            23 => 0.0048893333333335,
                            24 => 0.0048893333333335,
                            25 => 0.0048893333333335,
                        ),
                    ),
                    1 =>
                    array(
                        'name' => 'unimproved',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                            2 => NULL,
                            3 => NULL,
                            4 => NULL,
                            5 => NULL,
                            6 => NULL,
                            7 => NULL,
                            8 => NULL,
                            9 => NULL,
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
                        ),
                    ),
                    2 =>
                    array(
                        'name' => 'total',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                            2 => NULL,
                            3 => 0.012888846666667,
                            4 => 0.012888846666667,
                            5 => 0.012888846666667,
                            6 => 0.012888846666667,
                            7 => 0.012888846666667,
                            8 => 0.012888846666667,
                            9 => 0.011999973333333,
                            10 => 0.0111111,
                            11 => 0.010222226666667,
                            12 => 0.0093333533333333,
                            13 => 0.0084444800000001,
                            14 => 0.0075556066666667,
                            15 => 0.0066667333333335,
                            16 => 0.00577786,
                            17 => 0.0048889866666666,
                            18 => 0.0048889866666666,
                            19 => 0.0048889866666666,
                            20 => 0.0048889866666666,
                            21 => 0.0048889866666666,
                            22 => 0.0048889866666666,
                            23 => 0.0048889866666666,
                            24 => 0.0048889866666666,
                            25 => 0.0048889866666666,
                        ),
                    ),
                ),
            ),
            // This is to test that years without any value will not make infinite recursive calls
            array(1950, 1951, true, array(
                    0 =>
                    array(
                        'name' => 'improved',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                        ),
                    ),
                    1 =>
                    array(
                        'name' => 'unimproved',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                        ),
                    ),
                    2 =>
                    array(
                        'name' => 'total',
                        'data' =>
                        array(
                            0 => NULL,
                            1 => NULL,
                        ),
                    ),
                )),
        );
    }

    /**
     * @dataProvider flattenProvider
     */
    public function testComputeFlatten($yearStart, $yearEnd, $useQuestionnaires, $expected)
    {
        $this->assertEquals($expected, $this->service->computeFlatten($yearStart, $yearEnd, $this->filterSet, $useQuestionnaires ? $this->questionnaires : array()));
    }

    public function testCacheOnFilterForAllQuestionnaire()
    {
        $tmp = $this->flattenProvider();
        $data = reset($tmp);
        $res1 = $this->service->computeFlatten($data[0], $data[1], $this->filterSet, $this->questionnaires);

        $this->answer131->setValueAbsolute((0.2));
        $res2 = $this->service->computeFlatten($data[0], $data[1], $this->filterSet, $this->questionnaires);
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');


        $res3 = $this->service2->computeFlatten($data[0], $data[1], $this->filterSet, $this->questionnaires);

        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(array(
            0 =>
            array(
                'name' => 'improved',
                'data' =>
                array(
                    0 => 0.0037780000000005,
                    1 => 0.0008893333333333,
                ),
            ),
            1 =>
            array(
                'name' => 'unimproved',
                'data' =>
                array(
                    0 => NULL,
                    1 => NULL,
                ),
            ),
            2 =>
            array(
                'name' => 'total',
                'data' =>
                array(
                    0 => 0.0037778600000005,
                    1 => 0.00088898666666726,
                ),
            ),
                ), $res3, 'after clearing cache, result reflect new values');
    }

    public function testRealRepositoriesCanBeFound()
    {
        $service = new \Application\Service\Calculator\Jmp();
        $service->setServiceLocator($this->getApplicationServiceLocator());

        $this->assertInstanceOf('Application\Repository\PopulationRepository', $service->getPopulationRepository());
        $this->assertInstanceOf('Application\Repository\PartRepository', $service->getPartRepository());
    }

    public function testExcludeRules()
    {
        $exclude = new \Application\Model\Rule\Exclude();

        $filterRule = new \Application\Model\Rule\FilterRule();
        $filterRule->setQuestionnaire($this->questionnaire)->setRule($exclude)->setFilter($this->filter1);

        $this->assertEquals(array(
            'values' =>
            array(
                'tst 2' => 0.1,
            ),
            'values%' =>
            array(
                'tst 2' => 0.0066666666666667,
            ),
            'count' => 1,
            'years' =>
            array(
                0 => 2005,
            ),
            'minYear' => 2005,
            'maxYear' => 2005,
            'period' => 1,
            'slope' => null,
            'slope%' => null,
            'average' => 0.1,
            'average%' => 0.0066666666666667,
            'questionnaire' => array(
                'tst 2' => null,
            ),
            'population' => 15,
                ), $this->service->computeFilterForAllQuestionnaires($this->filter1, $this->questionnaires));
    }

    public function testComplementaryTotal()
    {
        $this->answer131->setPart($this->part1);
        $this->answer132->setPart($this->part2);

        $r1 = $this->service->computeFlatten(2006, 2007, $this->filterSet, $this->questionnaires, $this->part1);
        $r2 = $this->service->computeFlatten(2006, 2007, $this->filterSet, $this->questionnaires, $this->part2);
        $rt = $this->service->computeFlatten(2006, 2007, $this->filterSet, $this->questionnaires);

        $this->assertEquals(array(
            array(
                'name' => 'improved',
                'data' => array(
                    0.033333333333333,
                    0.033333333333333,
                ),
            ),
            array(
                'name' => 'unimproved',
                'data' => array(
                    NULL,
                    NULL,
                ),
            ),
            array(
                'name' => 'total',
                'data' => array(
                    0.033333333333333,
                    0.033333333333333,
                ),
            ),
                ), $r1);

        $this->assertEquals(array(
            array(
                'name' => 'improved',
                'data' => array(
                    0.0014285714285714,
                    0.0014285714285714,
                ),
            ),
            array(
                'name' => 'unimproved',
                'data' => array(
                    NULL,
                    NULL,
                ),
            ),
            array(
                'name' => 'total',
                'data' => array(
                    0.0014285714285714,
                    0.0014285714285714,
                ),
            ),
                ), $r2);

        $this->assertEquals(array(
            array(
                'name' => 'improved',
                'data' => array(
                    0.011,
                    0.011,
                ),
            ),
            array(
                'name' => 'unimproved',
                'data' => array(
                    NULL,
                    NULL,
                ),
            ),
            array(
                'name' => 'total',
                'data' => array(
                    0.011,
                    0.011,
                ),
            ),
                ), $rt);
    }

    public function testAllZeroValueShouldNotDivideByZero()
    {
        // Set everything to zero
        foreach ($this->questionnaires as $questionnaire) {
            foreach ($questionnaire->getAnswers() as $answer) {
                $answer->setValueAbsolute(0);
            }
        }

        // This call should NOT raise PHP warnings
        $this->assertEquals(array(
            'regression' => 0,
            'population' => 25,
                ), $this->service->computeRegression(2006, $this->filter1, $this->questionnaires));
    }

}
