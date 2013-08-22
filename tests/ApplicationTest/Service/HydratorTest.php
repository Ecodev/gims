<?php

namespace ApplicationTest\Service;

use Application\Model\Geoname;
use Application\Model\Questionnaire;
use Application\Model\Survey;
use Application\Service\Hydrator;

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
     * @var \Application\Model\User
     */
    private $user;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var array
     */
    private $fieldSet1 = array(
        'foo',
        'name',
        'questionnaires',
        'metadata',
        'questionnaires.foo',
        'questionnaires.name',
        'questionnaires.status',
        'questionnaires.metadata',
        'questionnaires.answers',
        'questionnaires.answers.foo',
        'questionnaires.answers.metadata',
        'questionnaires.answers.valuePercent',
        'questionnaires.answers.valueAbsolute',
    );

    /**
     * @var array
     */
    private $fieldSet2 = array(
        'name',
        'questionnaires',
    );

    public function setUp()
    {
        parent::setUp();

        $this->user = new \Application\Model\User();
        $this->user->setName('John');
        $this->choice1 = new \Application\Model\Question\Choice();
        $this->choice2 = new \Application\Model\Question\Choice();


        // Create a stub for the Hydrator class with predetermined values, so we don't have to mess with database
        $this->hydrator = $this->getMock('\Application\Service\Hydrator', array('getObject'), array(), '', false);
        $this->hydrator->expects($this->any())
                ->method('getObject')
                ->will($this->returnValueMap(array(
                            array('Application\Model\Question\Choice', 1, $this->choice1),
                            array('Application\Model\Question\Choice', 2, $this->choice2),
        )));
    }

    public function testCanHydrateAndExtract()
    {
        $data = array(
            'name' => 'John Connor',
            'email' => 'john.connor@skynet.net',
            'state' => null,
        );

        $this->hydrator->hydrate($data, $this->user);

        $this->assertEquals($data['name'], $this->user->getName());
        $this->assertEquals($data['email'], $this->user->getEmail());

        $actual = $this->hydrator->extract($this->user, array('name', 'email'));
        unset($actual['id']);
        $this->assertEquals($data, $actual, 'it must be exactly same as input, except the id');
    }

    public function testDoesNotModifySubobject()
    {
        $filter = new \Application\Model\Filter();
        $filter->setOfficialFilter($filter);

        $data = array(
            'name' => 'original name',
            'officialFilter' => array(
                'id' => 12345,
                'name' => 'this should not overwrite the original name',
            ),
        );

        $this->hydrator->hydrate($data, $filter);
        $this->assertEquals('original name', $filter->getName());
    }

    public function testHydrateUnknownPropertiesFailSilently()
    {

        $data = array(
            'foo' => 'bar',
            'name' => 'John Connor',
        );

        $this->hydrator->hydrate($data, $this->user);
        $this->assertEquals($data['name'], $this->user->getName());
        $this->assertArrayNotHasKey('foo', $this->hydrator->extract($this->user, array('foo')));
    }

    public function testSensitivePropertiesCannotBeHydrated()
    {
        $data = array(
            'id' => 12345,
            'password' => 'foo',
        );

        $this->hydrator->hydrate($data, $this->user);
        $this->assertNull($this->user->getId());
        $this->assertNull($this->user->getPassword());
    }

    public function testSensitivePropertiesCannotBeExtracted()
    {
        $data = array(
            'password',
        );

        $this->assertArrayNotHasKey('password', $this->hydrator->extract($this->user, $data));
    }

    public function testExtractArray()
    {
        $user2 = clone $this->user;
        $user2->setName('Bob');

        $this->assertEquals(array(
            0 =>
            array(
                'id' => null,
                'name' => 'John',
                'email' => null,
                'state' => null,
            ),
            1 =>
            array(
                'id' => null,
                'name' => 'Bob',
                'email' => null,
                'state' => null,
            ),
                )
                , $this->hydrator->extractArray(array($this->user, $user2), array('name')));
    }

    public function testCanHydrateCollectionExistingInDatabase()
    {
        $choices = new \Doctrine\Common\Collections\ArrayCollection();
        $choices->add($this->choice1);
        $choices->add($this->choice2);
        $question = new \Application\Model\Question\ChoiceQuestion();

        $data = array(
            'name' => 'What is your name ?',
            'choices' => array(
                1,
                2,
            )
        );
        $this->hydrator->hydrate($data, $question);

        $this->assertEquals($choices, $question->getChoices(), 'question should have the two new choices');
    }

    // @todo fix me or remove me
//    public function testExtractSubObject()
//    {
//        $filter1 = new \Application\Model\Filter('filter 1');
//        $filter2 = new \Application\Model\Filter('filter 2');
//        $filter1->setOfficialFilter($filter2);
//
//        $this->assertEquals(array(
//            'id' => null,
//            'name' => 'filter 1',
//            'officialFilter' => array(
//                'id' => null,
//                'name' => 'filter 2',
//            ),
//                ), $this->fixture->extract($filter1, array(
//                    'name',
//                    'officialFilter' => array('name'),
//                    'unkown properties' => array('foo', 'bar')
//                )), 'should return subobject, but only known one');
//    }

    /**
     * @test
     */
    public function extractSurveyWithPropertyQuestionnairesAndCheckResultContainsKeyQuestionnaires()
    {
        $actual = $this->hydrator->extract($this->getFakeSurvey(), array('name', 'questionnaires'));
        $this->assertArrayHasKey('questionnaires', $actual);
    }

    /**
     * @test
     */
    public function parsePropertiesAndCheckReturnContainsRecursivePropertyStructureForFieldSet2()
    {
        $this->hydrator->parseProperties('Application\Model\Survey', $this->fieldSet2);
        $actual = $this->hydrator->getPropertyStructure();

        $this->assertCount(2, $actual['Application\Model\Survey']);
        $this->assertCount(0, $actual['Application\Model\Questionnaire']);
    }

    /**
     * @test
     */
    public function parsePropertiesAndCheckReturnContainsRecursivePropertyStructureForFieldSet1()
    {
        $this->hydrator->parseProperties('Application\Model\Survey', $this->fieldSet1);
        $actual = $this->hydrator->getPropertyStructure();
        $this->assertCount(4, $actual['Application\Model\Survey']);
        $this->assertCount(5, $actual['Application\Model\Questionnaire']);
        $this->assertCount(4, $actual['Application\Model\Answer']);
    }

    /**
     * @test
     */
    public function completePropertyStructureWithDefaultPropertiesAndCheckWhetherReturnContainsId()
    {
        $this->hydrator->parseProperties('Application\Model\Survey', $this->fieldSet1);
        $this->hydrator->completePropertyStructureWithDefaultProperties();

        $actual = $this->hydrator->getPropertyStructure();
        foreach (array('Survey', 'Questionnaire', 'Answer') as $entity) {
            $this->assertContains('id', $actual['Application\Model\\' . $entity]);
        }
    }

    /**
     * @test
     */
    public function completePropertyStructureWithDefaultPropertiesAndCheckWhetherReturnResolveFieldAliasMetadata()
    {
        $properties = $this->hydrator->resolvePropertyAliases('\Application\Model\Survey', $this->fieldSet1);
        $this->hydrator->parseProperties('Application\Model\Survey', $properties);

        $actual = $this->hydrator->getPropertyStructure();
        foreach (array('Survey', 'Questionnaire', 'Answer') as $entity) {
            $this->assertContains(
                    'dateCreated', $actual['Application\Model\\' . $entity], 'Can not resolve metadata property alias'
            );
        }
    }

    /**
     * @test
     */
    public function checkPropertyPermissionMethodShouldRemoveFieldFoo()
    {
        $this->hydrator->parseProperties('Application\Model\Survey', $this->fieldSet1);
        $this->hydrator->completePropertyStructureWithDefaultProperties();
        $this->hydrator->checkPropertyPermission();

        $actual = $this->hydrator->getPropertyStructure();

        foreach (array('Survey', 'Questionnaire', 'Answer') as $entity) {
            $this->assertNotContains(
                    'foo', $actual['Application\Model\\' . $entity], 'Can not resolve metadata property alias'
            );
        }
    }

    /**
     * @test
     */
    public function getJsonConfigForEntityReturnsNotEmptyArray()
    {
        $this->hydrator->parseProperties('Application\Model\Survey', $this->fieldSet1);
        $this->hydrator->completePropertyStructureWithDefaultProperties();
        $this->hydrator->checkPropertyPermission();

        foreach (array('Survey', 'Questionnaire', 'Answer') as $entity) {
            $actual = $this->hydrator->getJsonConfigForEntity('Application\Model\\' . $entity);
            $this->assertNotEmpty($actual);
        }
    }

    /**
     * @test
     */
    public function extractSurveyWithPropertyQuestionnairesAndQuestionnairesNameAndCheckResultContainsKeyQuestionnairesName()
    {
        $actual = $this->hydrator->extract($this->getFakeSurvey(), array('name', 'questionnaires', 'questionnaires.name'));
        $this->assertInternalType('array', $actual['questionnaires']);
        $this->assertNotEmpty($actual['questionnaires']);
    }

    // @todo fix me or remove me
//    public function testExtractSubObjects()
//    {
//        $filter1 = new \Application\Model\Filter('filter 1');
//        $filter2 = new \Application\Model\Filter('filter 2');
//        $filter1->addChild($filter2);
//
//        $this->assertEquals(array(
//            'id' => null,
//            'name' => 'filter 1',
//            'children' => array(
//                array(
//                    'id' => null,
//                    'name' => 'filter 2',
//                ),
//            ),
//                ), $this->fixture->extract($filter1, array(
//                    'name',
//                    'children' => array('name'),
//                )), 'should return array of subobjects, but only known ones');
//    }

    public function testExtractSubObjectWithRecursiveConfiguration()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');
        $filter1->setOfficialFilter($filter2);

        $this->assertEquals(array(
            'id' => null,
            'name' => 'filter 1',
            'isOfficial' => true,
                // @todo check what to do
//            'officialFilter' => array(
//                'id' => null,
//                'name' => 'filter 2',
//                'officialFilter' => null,
//            ),
                ), $this->hydrator->extract($filter1, array(
                    'name',
                    'officialFilter' => '__recursive',
                )), 'should return same properties for children and parent');
    }

    public function testExtractBooleanProperty()
    {
        $filter1 = new \Application\Model\Filter('filter 1');

        $this->assertEquals(array(
            'id' => null,
            'name' => 'filter 1',
            'isOfficial' => true,
                ), $this->hydrator->extract($filter1, array(
                    'isOfficial',
                )), 'should use correct getter for boolean properties');
    }

    public function testExtractWithClosure()
    {
        $this->assertEquals(array(
            'id' => null,
            'name' => 'John',
            'email' => null,
            'state' => null,
            'custom name' => 'Mr. John Connor',
                ), $this->hydrator->extract($this->user, array(
                    'custom name' => function(Hydrator $hydrator, \Application\Model\User $user) {
                        return 'Mr. ' . $user->getName() . ' Connor';
                    },
                )), 'should allow custom properties via Closure');
    }

    public function testExtractDateTimeAsString()
    {
        $this->user->timestampCreation();
        $this->assertEquals(array(
            'id' => null,
            'email' => null,
            'state' => null,
            'dateCreated' => $this->user->getDateCreated()->format(\DateTime::ISO8601),
            'name' => 'John',
                ), $this->hydrator->extract($this->user, array(
                    'dateCreated',
                )), 'should serialize DateTime');
    }

    public function testHydrateAssociationWithSuboject()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');

        // Create a stub for the \Application\Service\Hydrator class, so we don't have to mess with database
        $mockHydrator = $this->getMock('\Application\Service\Hydrator', array('getObject'), array(), '', false);
        $mockHydrator->expects($this->any())
                ->method('getObject')
                ->will($this->returnValue($filter2));

        $mockHydrator->hydrate(array(
            'officialFilter' => array(
                'id' => 12345,
                'name' => 'name that should not be hydrated'
            ),
                ), $filter1);
        $this->assertEquals($filter2, $filter1->getOfficialFilter(), 'can set subobject');

        $filter1->setOfficialFilter(null);
        $this->assertNull($filter1->getOfficialFilter());
        $mockHydrator->hydrate(array('officialFilter' => 12345), $filter1);
        $this->assertEquals($filter2, $filter1->getOfficialFilter(), 'can also use short syntax with only ID');
        $this->assertEquals('filter 2', $filter1->getOfficialFilter()->getName(), 'properties of subobject should never be modified');
    }

    public function testCanHydrateBoolean()
    {
        $chapter = new \Application\Model\Question\Chapter();
        $this->assertFalse($chapter->isFinal(), 'default is false');

        $this->hydrator->hydrate(array('isFinal' => true), $chapter);
        $this->assertTrue($chapter->isFinal(), 'can set to true');

        $this->hydrator->hydrate(array('isFinal' => false), $chapter);
        $this->assertFalse($chapter->isFinal(), 'can set to true');

        $this->hydrator->hydrate(array('isFinal' => '1'), $chapter);
        $this->assertTrue($chapter->isFinal(), 'can set to 1');

        $this->hydrator->hydrate(array('isFinal' => '0'), $chapter);
        $this->assertFalse($chapter->isFinal(), 'can set to 0');
    }

    public function testCanExtractBoolean()
    {
        $chapter = new \Application\Model\Question\Chapter();

        $actual = $this->hydrator->extract($chapter, array('isFinal'));
        $this->assertArrayHasKey('isFinal', $actual);
        $this->assertFalse($actual['isFinal']);
    }

    /**
     * Return a fake survey for the sake of the test.
     *
     * @return Survey
     */
    private function getFakeSurvey()
    {

        $survey = new Survey();
        $survey->setIsActive(true);
        $survey->setName('test survey');
        $survey->setCode('code test survey');
        $survey->setYear(2010);

        $geoName = new Geoname('test geoname');

        $questionnaire = new Questionnaire();
        $questionnaire->setSurvey($survey);
        $questionnaire->setDateObservationStart(new \DateTime('2010-01-01T00:00:00+0100'));
        $questionnaire->setDateObservationEnd(new \DateTime('2011-01-01T00:00:00+0100'));
        $questionnaire->setGeoname($geoName);
        return $survey;
    }

}
