<?php

namespace ApplicationTest\Service\Calculator;

class JmpTest extends CalculatorTest
{

    /**
     * @var \Application\Model\Filter
     */
    private $filter;

    /**
     * @var \Application\Service\Calculator\Jmp
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->questionnaire2 = new \Application\Model\Questionnaire();


        // Define a second questionnaire with answers for leaf categories only
        $questionnaire2 = new \Application\Model\Questionnaire();

        $survey2 = new \Application\Model\Survey();
        $survey2->setCode('tst 2')->setName('Test survey 2')->setYear(2005);
        $questionnaire2->setSurvey($survey2);

        $question131 = new \Application\Model\Question();
        $question32 = new \Application\Model\Question();

        $question131->setCategory($this->category131);
        $question32->setCategory($this->category32);

        $answer131 = new \Application\Model\Answer();
        $answer32 = new \Application\Model\Answer();

        $answer131->setQuestionnaire($questionnaire2)->setQuestion($question131)->setValueAbsolute(0.1);
        $answer32->setQuestionnaire($questionnaire2)->setQuestion($question32)->setValueAbsolute(0.000001);


        $this->questionnaires = array($this->questionnaire, $questionnaire2);

        $this->filter = new \Application\Model\Filter('water');
        $this->filter->addCategoryFilterComponent($this->categoryFilterComponent1)
                ->addCategoryFilterComponent($this->categoryFilterComponent2)
                ->addCategoryFilterComponent($this->categoryFilterComponent3);

        $this->service = new \Application\Service\Calculator\Jmp();
        $this->service->setServiceLocator($this->getApplicationServiceLocator());

        // Clean existing population data
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneByCode('CH');
        $this->getEntityManager()->getConnection()->delete('population', array('country_id' => $country->getId()));

        // Populate with predetermined values
        $population1 = new \Application\Model\Population();
        $population1->setCountry($country)
                ->setPopulation(10)
                ->setYear($this->questionnaire->getSurvey()->getYear());
        $this->getEntityManager()->persist($population1);

        $population2 = new \Application\Model\Population();
        $population2->setCountry($country)
                ->setPopulation(10)
                ->setYear($questionnaire2->getSurvey()->getYear());
        $this->getEntityManager()->persist($population2);

        // Link questionnaire to country, so we are able to find population data via geonames
        $this->questionnaire->setGeoname($country->getGeoname());
        $questionnaire2->setGeoname($country->getGeoname());

        $this->getEntityManager()->flush();
    }

    public function testComputingCategoryFilterComponentForAllQuestionnairesIsCorrectt()
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
                'tst 2' => 0.01,
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
            'slope%' => -0.000222,
            'average' => 0.10555,
            'average%' => 0.010555,
            'population' => 20,
                ), $this->service->computeCategoryFilterComponentForAllQuestionnaires($this->categoryFilterComponent1, $this->questionnaires));

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
                ), $this->service->computeCategoryFilterComponentForAllQuestionnaires($this->categoryFilterComponent1, array()), 'no questionnaires should still return valid structure');
    }

    /**
     * Return input and output data for regression computation
     * @return array
     */
    public function regressionProvider()
    {
        return array(
            array(2000, 0.01111),
            array(2001, 0.010888),
            array(2002, 0.010666),
            array(2003, 0.010444),
            array(2004, 0.010222),
            array(2005, 0.01),
        );
    }

    /**
     * @dataProvider regressionProvider
     */
    public function testComputingRegressionForUnknownYearsIsCorrect($year, $expected)
    {
        $this->assertEquals($expected, $this->service->computeRegression($year, $this->categoryFilterComponent1, $this->questionnaires), 'regression between known years according');
        $this->assertNull($this->service->computeRegression($year, $this->categoryFilterComponent1, array()), 'no questionnaires should still return valid structure');
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
                            0 => 0.009778,
                            1 => 0.009556,
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
                            0 => 0.0097779,
                            1 => 0.0095557,
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
                            4 => 0.011554,
                            5 => 0.012664,
                            6 => 0.012442,
                            7 => 0.01222,
                            8 => 0.011554,
                            9 => 0.011332,
                            10 => 0.01111,
                            11 => 0.010888,
                            12 => 0.010666,
                            13 => 0.010444,
                            14 => 0.010222,
                            15 => 0.01,
                            16 => 0.009778,
                            17 => 0.009556,
                            18 => 0.00889,
                            19 => 0.008668,
                            20 => 0.008446,
                            21 => 0.009556,
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
                            4 => 0.0115555,
                            5 => 0.0126665,
                            6 => 0.0124443,
                            7 => 0.0122221,
                            8 => 0.0115555,
                            9 => 0.0113333,
                            10 => 0.0111111,
                            11 => 0.0108889,
                            12 => 0.0106667,
                            13 => 0.0104445,
                            14 => 0.0102223,
                            15 => 0.0100001,
                            16 => 0.0097779,
                            17 => 0.0095557,
                            18 => 0.0088891,
                            19 => 0.0086669,
                            20 => 0.0084447,
                            21 => 0.0095557,
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
    public function testComputingFlattenIsCorrect($yearStart, $yearEnd, $useQuestionnaires, $expected)
    {
        $this->assertEquals($expected, $this->service->computeFlatten($yearStart, $yearEnd, $this->filter, $useQuestionnaires ? $this->questionnaires : array()));
    }

    public function testCacheOnFilterComponentLevelCanBeDisabled()
    {
        $data = array_shift($this->flattenProvider());
        $res1 = $this->service->computeFlatten($data[0], $data[1], $this->filter, $this->questionnaires);

        $this->answer131->setValueAbsolute((0.2));
        $res2 = $this->service->computeFlatten($data[0], $data[1], $this->filter, $this->questionnaires);
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');


        $service2 = new \Application\Service\Calculator\Jmp();
        $service2->setServiceLocator($this->getApplicationServiceLocator());
        $res3 = $service2->computeFlatten($data[0], $data[1], $this->filter, $this->questionnaires);

        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(array(
            0 =>
            array(
                'name' => 'improved',
                'data' =>
                array(
                    0 => 0.0077780000000001,
                    1 => 0.0055560000000003,
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
                    0 => 0.0077779000000007,
                    1 => 0.0055557000000004,
                ),
            ),
                ), $res3, 'after clearing cache, result reflect new values');
    }

}
