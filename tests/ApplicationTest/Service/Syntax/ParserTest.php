<?php

namespace ApplicationTest\Service\Syntax;

/**
 * @group Service
 */
class ParserTest extends \ApplicationTest\Controller\AbstractController
{

    /**
     * Create a stub for the FilterRepository class with predetermined values, so we don't have to mess with database
     * @return \Application\Repository\FilterRepository
     */
    protected function getStubFilterRepository($shortModel)
    {

        $stubPopulationRepository = $this->getMock('\Application\Repository\\' . $shortModel . 'Repository', ['findOneById'], [], '', false);
        $stubPopulationRepository->expects($this->any())
                ->method('findOneById')
                ->will($this->returnCallback(function ($id) use ($shortModel) {
                            return $this->getModel('\Application\Model\\' . $shortModel, $id);
                        }));

        return $stubPopulationRepository;
    }

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetJsonStructure()
    {
        $parser = new \Application\Service\Syntax\Parser();
        $parser->setFilterRepository($this->getStubFilterRepository('Filter'));
        $parser->setQuestionnaireRepository($this->getStubFilterRepository('Questionnaire'));
        $parser->setPartRepository($this->getStubFilterRepository('Part'));
        $parser->setRuleRepository($this->getStubFilterRepository('Rule\Rule'));

        $f1 = $this->getNewModelWithId('\Application\Model\Filter');
        $f1->setName('filter 1');
        $f2 = $this->getNewModelWithId('\Application\Model\Filter');
        $f2->setName('filter 2');

        $survey = new \Application\Model\Survey('test survey');
        $survey->setCode('tst');
        $geoname = new \Application\Model\Geoname('test geoname');
        $questionnaire = $this->getNewModelWithId('\Application\Model\Questionnaire');
        $questionnaire->setSurvey($survey);
        $questionnaire->setGeoname($geoname);

        $part = $this->getNewModelWithId('\Application\Model\Part');
        $part->setName('test part');

        $q = $this->getNewModelWithId('\Application\Model\Rule\QuestionnaireUsage');
        $rule = $this->getNewModelWithId('\Application\Model\Rule\Rule');
        $rule->setName('test rule');
        $q->setRule($rule);

        $f1bis = $parser->getFilterRepository()->findOneById($f1->getId());
        $f2bis = $parser->getFilterRepository()->findOneById($f2->getId());
        $this->assertSame($f1, $f1bis);
        $this->assertSame($f2, $f2bis);

        $formula = '=IF(FOO(), SUM({F#1,Q#1,P#current}, {F#2,Q#current,P#1,S#2}), 0.95) + {self} + {Q#1,P#1} / {F#1,Q#1} + {R#1,Q#current,P#1}';
        $expected = [
            [
                'type' => 'text',
                'content' => '=IF(FOO(), SUM(',
            ],
            [
                'type' => 'filterValue',
                'filter' => [
                    'id' => 1,
                    'name' => 'filter 1',
                ],
                'questionnaire' => [
                    'id' => 1,
                    'name' => 'tst - test geoname',
                ],
                'part' => [
                    'id' => 'current',
                    'name' => 'current',
                ],
                'isSecondStep' => false,
                'highlightColor' => 'blue',
            ],
            [
                'type' => 'text',
                'content' => ', ',
            ],
            [
                'type' => 'filterValue',
                'filter' => [
                    'id' => 2,
                    'name' => 'filter 2',
                ],
                'questionnaire' => [
                    'id' => 'current',
                    'name' => 'current',
                ],
                'part' => [
                    'id' => 1,
                    'name' => 'test part',
                ],
                'isSecondStep' => true,
                'highlightColor' => 'red',
            ],
            [
                'type' => 'text',
                'content' => '), 0.95) + ',
            ],
            [
                'type' => 'self',
            ],
            [
                'type' => 'text',
                'content' => ' + ',
            ],
            [
                'type' => 'populationValue',
                'questionnaire' => [
                    'id' => 1,
                    'name' => 'tst - test geoname',
                ],
                'part' => [
                    'id' => 1,
                    'name' => 'test part',
                ],
            ],
            [
                'type' => 'text',
                'content' => ' / ',
            ],
            [
                'type' => 'questionName',
                'filter' => [
                    'id' => 1,
                    'name' => 'filter 1',
                ],
                'questionnaire' => [
                    'id' => 1,
                    'name' => 'tst - test geoname',
                ],
            ],
            [
                'type' => 'text',
                'content' => ' + ',
            ],
            [
                'type' => 'ruleValue',
                'rule' => [
                    'id' => 1,
                    'name' => 'test rule',
                ],
                'questionnaire' => [
                    'id' => 'current',
                    'name' => 'current',
                ],
                'part' => [
                    'id' => 1,
                    'name' => 'test part',
                ],
                'highlightColor' => 'green',
            ],
        ];

        $actual = $parser->getStructure($formula);
        $this->assertEquals($expected, $actual);

        $formula2 = '={self} + {Y} / {F#current,Q#all} * ({F#1,P#current,Y+2} + {Q#all,P#1})';
        $expected2 = [
            [
                'type' => 'text',
                'content' => '=',
            ],
            [
                'type' => 'self',
            ],
            [
                'type' => 'text',
                'content' => ' + ',
            ],
            [
                'type' => 'regressionYear',
            ],
            [
                'type' => 'text',
                'content' => ' / ',
            ],
            [
                'type' => 'regressionFilterValuesList',
                'filter' => [
                    'id' => 'current',
                    'name' => 'current',
                ],
            ],
            [
                'type' => 'text',
                'content' => ' * (',
            ],
            [
                'type' => 'regressionFilterValue',
                'filter' => [
                    'id' => 1,
                    'name' => 'filter 1',
                ],
                'part' => [
                    'id' => 'current',
                    'name' => 'current',
                ],
                'year' => '+2',
            ],
            [
                'type' => 'text',
                'content' => ' + ',
            ],
            [
                'type' => 'regressionCumulatedPopulation',
                'part' => [
                    'id' => 1,
                    'name' => 'test part',
                ]
            ],
            [
                'type' => 'text',
                'content' => ')',
            ],
        ];

        $actual2 = $parser->getStructure($formula2);
        $this->assertEquals($expected2, $actual2);

        $formula = '={F#1,Q#1,P#current} + {F#2,Q#1,P#current} + {F#1,Q#1,P#current}';
        $actuals = $parser->getStructure($formula);
        $this->assertEquals('blue', $actuals[1]['highlightColor']);
        $this->assertEquals('red', $actuals[3]['highlightColor'], 'different tokens must have different colors');
        $this->assertEquals('blue', $actuals[5]['highlightColor'], 'identical tokens must have identical colors');
    }

}
