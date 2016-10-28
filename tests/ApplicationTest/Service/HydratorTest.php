<?php

namespace ApplicationTest\Service;

use Application\Model\Geoname;
use Application\Model\Questionnaire;
use Application\Model\Survey;
use Application\Service\Hydrator;

/**
 * @group Service
 */
class HydratorTest extends \ApplicationTest\Controller\AbstractController
{

    /**
     * @var \Application\Model\Question\Choice
     */
    private $choice1;

    /**
     * @var \Application\Model\Question\Choice
     */
    private $choice2;

    /**
     * @var Hydrator
     */
    private $hydrator;

    public function setUp()
    {
        parent::setUp();

        $this->user = new \Application\Model\User('John');
        $this->choice1 = new \Application\Model\Question\Choice();
        $this->choice2 = new \Application\Model\Question\Choice();

        // Create a stub for the Hydrator class with predetermined values, so we don't have to mess with database
        $this->hydrator = $this->getMock('\Application\Service\Hydrator', ['getObject'], [], '', false);
        $this->hydrator->expects($this->any())
                ->method('getObject')
                ->will($this->returnValueMap([
                            [\Application\Model\Question\Choice::class, 1, $this->choice1],
                            [\Application\Model\Question\Choice::class, 2, $this->choice2],
        ]));
    }

    public function testDot()
    {
        $closure = function () {
            return 123;
        };

        $actual = $this->hydrator->initializePropertyStructure([
            'name',
            'subobject',
            'subobject.name',
            'subobject.subsubobject.name',
            'othersub.name',
            'closure' => $closure,
            'children.__recursive',
            'new1.new2.new3.new4', // new object, not referenced before, should not have any issue
        ]);

        $expected = [
            'name',
            'subobject' => [
                'name',
                'subsubobject' => [
                    'name',
                ],
            ],
            'othersub' => [
                'name',
            ],
            'closure' => $closure,
            'children' => '__recursive',
            'new1' => [
                'new2' => [
                    'new3' => [
                        'new4',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testMetadata()
    {
        $in = [
            'metadata',
            'creator.gravatar',
        ];
        $expected = [
            'dateCreated',
            'dateModified',
            'creator' => [
                'gravatar',
            ],
            3 => 'modifier',
        ];

        $actual = $this->hydrator->initializePropertyStructure($in);
        $this->assertEquals($expected, $actual);
    }

    public function testCanHydrateAndExtract()
    {
        $data = [
            'name' => 'John Connor',
            'email' => 'john.connor@skynet.net',
            'state' => null,
            'lastLogin' => '1997-08-29T01:02:03+0000',
        ];

        $this->hydrator->hydrate($data, $this->user);

        $this->assertEquals($data['name'], $this->user->getName());
        $this->assertEquals($data['email'], $this->user->getEmail());

        $actual = $this->hydrator->extract($this->user, ['name', 'email']);
        unset($actual['id']);
        $this->assertEquals($data, $actual, 'it must be exactly same as input, except the id');
    }

    public function testCanHydrateAndExtractJSONproperty()
    {
        $data = [
            'alternateNames' => [
                123 => 'alternate 1',
                456 => 'alternate 2',
            ],
        ];

        $question = new \Application\Model\Question\NumericQuestion('tst question');
        $this->assertEquals(\Application\Model\Question\AbstractQuestion::EMPTY_ASSOCIATIVE_ARRAY, $question->getAlternateNames());

        $this->hydrator->hydrate($data, $question);
        $this->assertEquals($data['alternateNames'], $question->getAlternateNames());

        $actual = $this->hydrator->extract($question, ['alternateNames']);
        $this->assertEquals($data['alternateNames'], $actual['alternateNames'], 'it must be exactly same as input');
    }

    public function testDoesNotModifySubobject()
    {
        $survey = new Survey('original name');
        $questionnaire = new \Application\Model\Questionnaire();
        $questionnaire->setSurvey($survey);

        $data = [
//            'comments' => 'some comments',
            'survey' => [
                'id' => 12345,
                'name' => 'this should not overwrite the original name',
            ],
        ];

        // Create a stub for the \Application\Service\Hydrator class, so we don't have to mess with database
        $mockHydrator = $this->getMock('\Application\Service\Hydrator', ['getObject'], [], '', false);
        $mockHydrator->expects($this->any())
                ->method('getObject')
                ->will($this->returnValue($survey));

        $mockHydrator->hydrate($data, $questionnaire);
        $this->assertEquals('original name', $survey->getName());
    }

    public function testHydrateUnknownPropertiesFailSilently()
    {
        $data = [
            'foo' => 'bar',
            'name' => 'John Connor',
        ];

        $this->hydrator->hydrate($data, $this->user);
        $this->assertEquals($data['name'], $this->user->getName());
        $this->assertArrayNotHasKey('foo', $this->hydrator->extract($this->user, ['foo']));
    }

    public function testSensitivePropertiesCannotBeHydrated()
    {
        $data = [
            'id' => 12345,
            'password' => 'foo',
            'activationToken' => 'foo',
        ];

        $this->hydrator->hydrate($data, $this->user);
        $this->assertNull($this->user->getId());
        $this->assertNull($this->user->getPassword());
    }

    public function testSensitivePropertiesCannotBeExtracted()
    {
        $data = [
            'password',
            'activationToken',
        ];

        $this->assertArrayNotHasKey('password', $this->hydrator->extract($this->user, $data));
        $this->assertArrayNotHasKey('activationToken', $this->hydrator->extract($this->user, $data));
    }

    public function testExtractArray()
    {
        $user2 = clone $this->user;
        $user2->setName('Bob');

        $this->assertEquals([
            0 => [
                'id' => null,
                'name' => 'John',
                'email' => null,
                'state' => 0,
                'lastLogin' => null,
            ],
            1 => [
                'id' => null,
                'name' => 'Bob',
                'email' => null,
                'state' => 0,
                'lastLogin' => null,
            ],
        ], $this->hydrator->extractArray([$this->user, $user2], ['name']));
    }

    public function testCanHydrateCollectionExistingInDatabase()
    {
        $choices = new \Doctrine\Common\Collections\ArrayCollection();
        $choices->add($this->choice1);
        $choices->add($this->choice2);
        $question = new \Application\Model\Question\ChoiceQuestion();

        $data = [
            'name' => 'What is your name ?',
            'choices' => [
                1,
                2,
            ],
        ];
        $this->hydrator->hydrate($data, $question);

        $this->assertEquals($choices, $question->getChoices(), 'question should have the two new choices');
    }

    public function testExtractSubObject()
    {
        $questionnaire = new \Application\Model\Questionnaire('filter 1');
        $questionnaire->setComments('test comments');
        $survey = new \Application\Model\Survey('test survey');
        $survey->setCode('tst');
        $questionnaire->setSurvey($survey);
        $questionnaire->setGeoname(new Geoname('test geoname'));

        $this->assertEquals([
            'id' => null,
            'name' => 'tst - test geoname',
            'comments' => 'test comments',
            'status' => 'new',
            'survey' => [
                'id' => null,
                'name' => 'test survey',
                'code' => 'tst',
                'isActive' => false,
                'year' => null,
                'dateStart' => null,
                'dateEnd' => null,
            ],
                ], $this->hydrator->extract($questionnaire, [
                    'comments',
                    'survey' => ['name'],
                    'unkown properties' => ['foo', 'bar'],
                ]), 'should return subobject, but only known one');
    }

    /**
     * @test
     */
    public function extractSurveyWithPropertyQuestionnairesAndCheckResultContainsKeyQuestionnaires()
    {
        $actual = $this->hydrator->extract($this->getFakeSurvey(), ['name', 'questionnaires']);
        $this->assertArrayHasKey('questionnaires', $actual);
    }

    /**
     * @test
     */
    public function extractSurveyWithPropertyQuestionnairesAndQuestionnairesNameAndCheckResultContainsKeyQuestionnairesName()
    {
        $actual = $this->hydrator->extract($this->getFakeSurvey(), ['name', 'questionnaires', 'questionnaires.name']);
        $this->assertInternalType('array', $actual['questionnaires']);
        $this->assertNotEmpty($actual['questionnaires']);
    }

    public function testExtractSubObjects()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');
        $filter1->addChild($filter2);

        $this->assertEquals([
            'id' => null,
            'name' => 'filter 1',
            'children' => [
                [
                    'id' => null,
                    'name' => 'filter 2',
                ],
            ],
                ], $this->hydrator->extract($filter1, [
                    'name',
                    'children' => ['name'],
                ]), 'should return array of subobjects, but only known ones');
    }

    public function testExtractSubObjectWithRecursiveConfiguration()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');
        $filter1->addChild($filter2);

        $expected = [
            'id' => null,
            'name' => 'filter 1',
            'color' => $filter1->getColor(),
            'children' => [
                [
                    'id' => null,
                    'name' => 'filter 2',
                    'color' => $filter2->getColor(),
                    'children' => [],
                ],
            ],
        ];

        $this->assertEquals($expected, $this->hydrator->extract($filter1, [
                    'color',
                    'children' => '__recursive',
                ]), 'should return same properties for children and parent');
    }

    public function testExtractBooleanProperty()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter1->setColor('c0ffee');

        $this->assertEquals([
            'id' => null,
            'name' => 'filter 1',
            'color' => 'c0ffee',
                ], $this->hydrator->extract($filter1, [
                    'color',
                ]), 'should use correct getter for boolean properties');
    }

    public function testExtractWithClosure()
    {
        $this->assertEquals([
            'id' => null,
            'name' => 'John',
            'email' => null,
            'state' => 0,
            'lastLogin' => null,
            'custom name' => 'Mr. John Connor',
                ], $this->hydrator->extract($this->user, [
                    'custom name' => function (Hydrator $hydrator, \Application\Model\User $user) {
                        return 'Mr. ' . $user->getName() . ' Connor';
                    },
                ]), 'should allow custom properties via Closure');
    }

    public function testExtractDateTimeAsString()
    {
        $this->user->timestampCreation();
        $this->assertEquals([
            'id' => null,
            'email' => null,
            'state' => 0,
            'lastLogin' => null,
            'dateCreated' => $this->user->getDateCreated()->format(\DateTime::ISO8601),
            'name' => 'John',
                ], $this->hydrator->extract($this->user, [
                    'dateCreated',
                ]), 'should serialize DateTime');
    }

    public function testHydrateAssociationWithSuboject()
    {
        $questionnaire = new \Application\Model\Questionnaire();
        $survey = new \Application\Model\Survey('test survey');

        // Create a stub for the \Application\Service\Hydrator class, so we don't have to mess with database
        $mockHydrator = $this->getMock('\Application\Service\Hydrator', ['getObject'], [], '', false);
        $mockHydrator->expects($this->any())
                ->method('getObject')
                ->will($this->returnValue($survey));

        $mockHydrator->hydrate([
            'survey' => [
                'id' => 12345,
                'name' => 'name that should not be hydrated',
            ],
                ], $questionnaire);
        $this->assertEquals($survey, $questionnaire->getSurvey(), 'can set subobject');

        $questionnaire = new \Application\Model\Questionnaire();
        $this->assertNull($questionnaire->getSurvey());
        $mockHydrator->hydrate(['survey' => 12345], $questionnaire);
        $this->assertEquals($survey, $questionnaire->getSurvey(), 'can also use short syntax with only ID');
        $this->assertEquals('test survey', $questionnaire->getSurvey()->getName(), 'properties of subobject should never be modified');
    }

    public function testCanHydrateBoolean()
    {
        $chapter = new \Application\Model\Question\Chapter();
        $this->assertFalse($chapter->isFinal(), 'default is false');

        $this->hydrator->hydrate(['isFinal' => true], $chapter);
        $this->assertTrue($chapter->isFinal(), 'can set to true');

        $this->hydrator->hydrate(['isFinal' => false], $chapter);
        $this->assertFalse($chapter->isFinal(), 'can set to true');

        $this->hydrator->hydrate(['isFinal' => '1'], $chapter);
        $this->assertTrue($chapter->isFinal(), 'can set to 1');

        $this->hydrator->hydrate(['isFinal' => '0'], $chapter);
        $this->assertFalse($chapter->isFinal(), 'can set to 0');
    }

    public function testCanExtractBoolean()
    {
        $chapter = new \Application\Model\Question\Chapter();

        $actual = $this->hydrator->extract($chapter, ['isFinal']);
        $this->assertArrayHasKey('isFinal', $actual);
        $this->assertFalse($actual['isFinal']);
    }

    public function testCanExtractNonBooleanStartingWithIs()
    {
        $geoname = new \Application\Model\Geoname();
        $geoname->setIso3('abc');

        $actual = $this->hydrator->extract($geoname, ['iso3']);
        $this->assertArrayHasKey('iso3', $actual);
        $this->assertEquals($geoname->getIso3(), $actual['iso3']);
    }

    /**
     * Return a fake survey for the sake of the test.
     *
     * @return Survey
     */
    private function getFakeSurvey()
    {
        $survey = new Survey('test survey');
        $survey->setIsActive(true);
        $survey->setCode('code test survey');
        $survey->setYear(2010);

        $geoname = new Geoname('test geoname');

        $questionnaire = new Questionnaire();
        $questionnaire->setSurvey($survey);
        $questionnaire->setDateObservationStart(new \DateTime('2010-01-01T00:00:00+0100'));
        $questionnaire->setDateObservationEnd(new \DateTime('2011-01-01T00:00:00+0100'));
        $questionnaire->setGeoname($geoname);

        return $survey;
    }
}
