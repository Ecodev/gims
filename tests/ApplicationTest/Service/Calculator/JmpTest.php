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

        // Define a second questionnaire with answers for leaf filters only
        // Create a stub for the Questionnaire class with fake ID, so we don't have to mess with database
        $questionnaire2 = $this->getMock('\Application\Model\Questionnaire', array('getId'));
        $questionnaire2->expects($this->any())
                ->method('getId')
                ->will($this->returnValue(2));

        $survey2 = new \Application\Model\Survey();
        $survey2->setCode('tst 2')->setName('Test survey 2')->setYear(2005);
        $questionnaire2->setSurvey($survey2)->setGeoname($this->geoname);

        $question131 = new \Application\Model\Question\NumericQuestion();
        $question32 = new \Application\Model\Question\NumericQuestion();

        $question131->setFilter($this->filter131);
        $question32->setFilter($this->filter32);

        $answer131 = new \Application\Model\Answer();
        $answer32 = new \Application\Model\Answer();

        $answer131->setPart($this->part)->setQuestionnaire($questionnaire2)->setQuestion($question131)->setValuePercent(0.1);
        $answer32->setPart($this->part)->setQuestionnaire($questionnaire2)->setQuestion($question32)->setValuePercent(0.000001);

        $this->questionnaires = array($this->questionnaire, $questionnaire2);

        $this->filterSet = new \Application\Model\FilterSet('water');
        $this->filterSet->addFilter($this->highFilter1)
                ->addFilter($this->highFilter2)
                ->addFilter($this->highFilter3);

        $this->part1 = new \Application\Model\Part('tst part 1');
        $this->part2 = new \Application\Model\Part('tst part 2');

        // Create a stub for the PartRepository class, so we don't have to mess with database
        $stubPartRepository = $this->getMock('\Application\Repository\PartRepository', array('getAllNonTotal'), array(), '', false);
        $stubPartRepository->expects($this->any())
                ->method('getAllNonTotal')
                ->will($this->returnValue(array($this->part1, $this->part2)));

        // Create a stub for the PopulationRepository class with predetermined values, so we don't have to mess with database
        $stubPopulationRepository = $this->getMock('\Application\Repository\PopulationRepository', array('getOneByGeoname'), array(), '', false);
        $stubPopulationRepository->expects($this->any())
                ->method('getOneByGeoname')
                ->will($this->returnValueMap(array(
                            array($this->geoname, $this->part, 2000, (new \Application\Model\Population())->setPopulation(10)),
                            array($this->geoname, $this->part, 2001, (new \Application\Model\Population())->setPopulation(10)),
                            array($this->geoname, $this->part, 2005, (new \Application\Model\Population())->setPopulation(15)),
                            array($this->geoname, $this->part1, 2000, (new \Application\Model\Population())->setPopulation(3)),
                            array($this->geoname, $this->part1, 2001, (new \Application\Model\Population())->setPopulation(3)),
                            array($this->geoname, $this->part1, 2005, (new \Application\Model\Population())->setPopulation(3)),
                            array($this->geoname, $this->part2, 2000, (new \Application\Model\Population())->setPopulation(7)),
                            array($this->geoname, $this->part2, 2001, (new \Application\Model\Population())->setPopulation(7)),
                            array($this->geoname, $this->part2, 2005, (new \Application\Model\Population())->setPopulation(12)),
        )));

        $this->assertEquals(10, $stubPopulationRepository->getOneByGeoname($this->geoname, $this->part, 2000)->getPopulation());
        $this->assertEquals(3, $stubPopulationRepository->getOneByGeoname($this->geoname, $this->part1, 2000)->getPopulation());
        $this->assertEquals(7, $stubPopulationRepository->getOneByGeoname($this->geoname, $this->part2, 2000)->getPopulation());

        $this->assertEquals(15, $stubPopulationRepository->getOneByGeoname($this->geoname, $this->part, 2005)->getPopulation());
        $this->assertEquals(3, $stubPopulationRepository->getOneByGeoname($this->geoname, $this->part1, 2005)->getPopulation());
        $this->assertEquals(12, $stubPopulationRepository->getOneByGeoname($this->geoname, $this->part2, 2005)->getPopulation());

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
                ), $this->service->computeFilterForAllQuestionnaires($this->highFilter1, $this->questionnaires, $this->part));

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
                ), $this->service->computeFilterForAllQuestionnaires($this->highFilter1, array(), $this->part), 'no questionnaires should still return valid structure');
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
        $this->assertEquals($expected, $this->service->computeRegressionOneYear($year, $this->highFilter1, $this->questionnaires, $this->part), 'regression between known years according');
        $this->assertNull($this->service->computeRegressionOneYear($year, $this->highFilter1, array(), $this->part), 'no questionnaires should still return valid structure');
    }

    public function testComputeRegressionForShortPeriod()
    {
        $this->questionnaire->getSurvey()->setYear(2003);
        $this->assertEquals(0.105556, $this->service->computeRegressionOneYear(2004, $this->highFilter3, $this->questionnaires, $this->part), 'regression between known years according');
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
            array(2006, 2007, true, array(
                    0 =>
                    array(
                        'name' => 'improved',
                        'data' =>
                        array(
                            0 => 0.09778,
                            1 => 0.095560000000001,
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
                            0 => 0.097779,
                            1 => 0.095557,
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
                            3 => NULL,
                            4 => 0.11554,
                            5 => 0.11554,
                            6 => 0.11554,
                            7 => 0.11554,
                            8 => 0.11554,
                            9 => 0.11332,
                            10 => 0.1111,
                            11 => 0.10888,
                            12 => 0.10666,
                            13 => 0.10444,
                            14 => 0.10222,
                            15 => 0.1,
                            16 => 0.09778,
                            17 => 0.095560000000001,
                            18 => 0.095560000000001,
                            19 => 0.095560000000001,
                            20 => 0.095560000000001,
                            21 => 0.095560000000001,
                            22 => null,
                            23 => null,
                            24 => null,
                            25 => null,
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
                            3 => null,
                            4 => 0.115555,
                            5 => 0.115555,
                            6 => 0.115555,
                            7 => 0.115555,
                            8 => 0.115555,
                            9 => 0.113333,
                            10 => 0.111111,
                            11 => 0.108889,
                            12 => 0.106667,
                            13 => 0.104445,
                            14 => 0.102223,
                            15 => 0.100001,
                            16 => 0.097779,
                            17 => 0.095557,
                            18 => 0.095557,
                            19 => 0.095557,
                            20 => 0.095557,
                            21 => 0.095557,
                            22 => null,
                            23 => null,
                            24 => null,
                            25 => null,
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
        $this->assertEquals($expected, $this->service->computeFlattenAllYears($yearStart, $yearEnd, $this->filterSet, $useQuestionnaires ? $this->questionnaires : array(), $this->part));
    }

    public function testCacheOnFilterForAllQuestionnaire()
    {
        $tmp = $this->flattenProvider();
        $data = reset($tmp);
        $res1 = $this->service->computeFlattenAllYears($data[0], $data[1], $this->filterSet, $this->questionnaires, $this->part);

        $this->answer131->setValuePercent((0.2));
        $res2 = $this->service->computeFlattenAllYears($data[0], $data[1], $this->filterSet, $this->questionnaires, $this->part);
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');

        $res3 = $this->service2->computeFlattenAllYears($data[0], $data[1], $this->filterSet, $this->questionnaires, $this->part);

        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(array(
            0 =>
            array(
                'name' => 'improved',
                'data' =>
                array(
                    0 => 0.077779999999997,
                    1 => 0.05556,
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
                    0 => 0.077778999999992,
                    1 => 0.055556999999993,
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
        $filterRule->setPart($this->part)->setQuestionnaire($this->questionnaire)->setRule($exclude)->setFilter($this->filter1);

        $this->assertEquals(array(
            'values' =>
            array(
                2 => 0.1,
            ),
            'count' => 1,
            'years' =>
            array(
                2 => 2005,
            ),
            'minYear' => 2005,
            'maxYear' => 2005,
            'period' => 1,
            'slope' => null,
            'average' => 0.1,
            'surveys' => array(
                2 => 'tst 2',
            ),
                ), $this->service->computeFilterForAllQuestionnaires($this->filter1, $this->questionnaires, $this->part));
    }

    public function testComplementaryTotal()
    {
        $this->answer131->setPart($this->part1);
        $this->answer132->setPart($this->part2);

        $r1 = $this->service->computeFlattenAllYears(2000, 2001, $this->filterSet, $this->questionnaires, $this->part1);
        $r2 = $this->service->computeFlattenAllYears(2000, 2001, $this->filterSet, $this->questionnaires, $this->part2);
        $rt = $this->service->computeFlattenAllYears(2000, 2001, $this->filterSet, $this->questionnaires, $this->part);

        $this->assertEquals(array(
            array(
                'name' => 'improved',
                'data' => array(
                    0 => 0.1,
                    1 => 0.1,
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
                    0 => 0.1,
                    1 => 0.1,
                ),
            ),
                ), $r1);

        $this->assertEquals(array(
            array(
                'name' => 'improved',
                'data' => array(
                    0 => 0.01,
                    1 => 0.01,
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
                    0 => 0.01,
                    1 => 0.01,
                ),
            ),
                ), $r2);

        $this->assertEquals(array(
            array(
                'name' => 'improved',
                'data' => array(
                    0 => 0.037,
                    1 => 0.037,
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
                    0 => 0.037,
                    1 => 0.037,
                ),
            ),
                ), $rt);
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
        $this->assertEquals(0, $this->service->computeRegressionOneYear(2006, $this->filter1, $this->questionnaires, $this->part));
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
        $this->assertEquals(4, $this->service->computeRegressionOneYear(2006, $this->filter1, $this->questionnaires, $this->part));
    }

}
