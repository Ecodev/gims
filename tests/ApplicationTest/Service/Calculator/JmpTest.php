<?php

namespace ApplicationTest\Service\Calculator;

class JmpTest extends AbstractCalculator
{

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

        // Clean existing population data
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneByCode('CH');
        $this->getEntityManager()->getConnection()->delete('population', array('country_id' => $country->getId()));

        // Populate with predetermined values
        $population1 = new \Application\Model\Population();
        $population1->setCountry($country)
                ->setPopulation(10)
                ->setYear($this->questionnaire->getSurvey()->getYear());

        $population2 = new \Application\Model\Population();
        $population2->setCountry($country)
                ->setPopulation(15)
                ->setYear($questionnaire2->getSurvey()->getYear());
        $this->country = $country;


        // Create a stub for the PopulationRepository class, so we don't have to mess with database
        $stubPopulationRepository = $this->getMock('\Application\Repository\PopulationRepository', array('getOneByQuestionnaire'), array(), '', false);
        $stubPopulationRepository->expects($this->any())
                ->method('getOneByQuestionnaire')
                ->will($this->returnValueMap(array(
                            array($this->questionnaire, null, $population1),
                            array($questionnaire2, null, $population2),
        )));

        $this->service = new \Application\Service\Calculator\Jmp();
        $this->service->setPopulationRepository($stubPopulationRepository);
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
            'slope%' => -0.00088866666666667,
            'average' => 0.10555,
            'average%' => 0.0088883333333333,
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
            array(2000, 0.01111),
            array(2001, 0.010221333333333),
            array(2002, 0.0093326666666669),
            array(2003, 0.0084440000000001),
            array(2004, 0.0075553333333336),
            array(2005, 0.0066666666666668),
        );
    }

    /**
     * @dataProvider regressionProvider
     */
    public function testComputeRegressionForUnknownYears($year, $expected)
    {
        $this->assertEquals($expected, $this->service->computeRegression($year, $this->highFilter1, $this->questionnaires), 'regression between known years according');
        $this->assertNull($this->service->computeRegression($year, $this->highFilter1, array()), 'no questionnaires should still return valid structure');
    }

    public function testComputeRegressionForShortPeriod()
    {
        $this->questionnaire->getSurvey()->setYear(2003);
        $this->assertEquals(0.0088889166666667, $this->service->computeRegression(2004, $this->highFilter3, $this->questionnaires), 'regression between known years according');
    }

    public function computeFlattenOneYearProvider()
    {
        return array(
            // Basic casses
            array(array(2000 => 0, 2001 => 1, 2002 => 0.5, 1950 => null), array(2000 => -10, 2001 => 10, 2002 => 0.5)),
            // Cases with undefined values, based on the year earlier
            array(array(2001 => null, 2004 => 0.04, 2006 => 0.96), array(2000 => 0.5, 2001 => null, 2002 => 0.5, 2003 => 0.04, 2004 => null, 2005 => 0.96, 2006 => null)),
            array(array(2002 => 0.04), array(2000 => 0.01, 2001 => 0.04, 2002 => null)),
            array(array(2002 => 0.96), array(2000 => 0.99, 2001 => 0.96, 2002 => null)),
            array(array(2001 => 1), array(2000 => 1, 2001 => null)),
            array(array(2001 => 0), array(2000 => 0, 2001 => null)),
            // Cases with undefined values, based on the year later
            array(array(2000 => null, 2002 => 0.04, 2005 => 0.96), array(2000 => null, 2001 => 0.5, 2002 => null, 2003 => 0.04, /* NO 2004 ! */ 2005 => null, 2006 => 0.96)),
            array(array(2000 => 0.04), array(2000 => null, 2001 => 0.04, 2002 => 0.01)),
            array(array(2000 => 0.96), array(2000 => null, 2001 => 0.96, 2002 => 0.99)),
            array(array(2000 => 1), array(2000 => null, 2001 => 1)),
            array(array(2000 => 0), array(2000 => null, 2001 => 0)),
        );
    }

    /**
     * @dataProvider computeFlattenOneYearProvider
     */
    public function testComputeFlattenOneYear($stuff, $allRegressions)
    {
        foreach ($stuff as $year => $expected) {
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
                            3 => NULL,
                            4 => 0.012887333333333,
                            5 => 0.017330666666667,
                            6 => 0.016442,
                            7 => 0.015553333333334,
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
                            18 => 0.0022233333333335,
                            19 => 0.0013346666666667,
                            20 => 0.00044600000000017,
                            21 => 0.0048893333333335,
                            22 => NULL,
                            23 => NULL,
                            24 => NULL,
                            25 => NULL,
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
                            3 => NULL,
                            4 => 0.012888846666667,
                            5 => 0.017333213333333,
                            6 => 0.01644434,
                            7 => 0.015555466666667,
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
                            18 => 0.0022223666666668,
                            19 => 0.0013334933333333,
                            20 => 0.00044462000000012,
                            21 => 0.0048889866666666,
                            22 => NULL,
                            23 => NULL,
                            24 => NULL,
                            25 => NULL,
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

}
