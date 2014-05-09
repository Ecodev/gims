<?php

namespace ApiTest\Controller;

use Zend\Http\Request;
use Api\Service\MetaModel;
use Application\Model\Answer;
use Application\Model\Filter;
use Application\Model\FilterSet;
use Application\Model\Geoname;
use Application\Model\Part;
use Application\Model\Question\NumericQuestion;
use Application\Model\Questionnaire;
use Application\Model\Survey;
use Application\Model\UserSurvey;
use Application\Model\UserQuestionnaire;
use Application\Model\Rule\Rule;
use Application\Model\Rule\QuestionnaireUsage;
use Application\Model\Rule\FilterQuestionnaireUsage;
use Application\Model\Rule\FilterGeonameUsage;

abstract class AbstractRestfulControllerTest extends \ApplicationTest\Controller\AbstractController
{

    /**
     * @var Survey
     */
    protected $survey;

    /**
     * @var Questionnaire
     */
    protected $questionnaire;

    /**
     * @var NumericQuestion
     */
    protected $question;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var FilterSet
     */
    protected $filterSet;

    /**
     * @var Part
     */
    protected $part;

    /**
     * @var Part
     */
    protected $part2;

    /**
     * @var Part
     */
    protected $part3;

    /**
     * @var Answer
     */
    protected $answer;

    /**
     * Answer without part
     * @var Answer
     */
    private $answer2;

    /**
     * @var UserSurvey
     */
    protected $userSurvey;

    /**
     * @var UserQuestionnaire
     */
    protected $userQuestionnaire1;

    /**
     * @var UserQuestionnaire
     */
    protected $userQuestionnaire2;

    /**
     * @var UserFilterSet
     */
    protected $userFilterSet;

    /**
     * @var Geoname
     */
    protected $geoname;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var QuestionnaireUsage
     */
    protected $questionnaireUsage;

    /**
     * @var FilterQuestionnaireUsage
     */
    protected $filterQuestionnaireUsage;

    /**
     * @var FilterGeonameUsage
     */
    protected $filterGeonameUsage;

    /**
     * @var metaModel
     */
    protected $metaModel;

    public function setUp()
    {
        parent::setUp();
        $this->populateStorage();
    }

    /**
     * @return void
     */
    protected function populateStorage()
    {
        $this->metaModel = new MetaModel();

        $this->survey = new Survey();
        $this->survey->setIsActive(true);
        $this->survey->setName('test survey');
        $this->survey->setCode('code test survey');
        $this->survey->setYear(2010);

        $this->geoname = new Geoname('test geoname');

        $this->filter = new Filter('foo');
        $this->filter2 = new Filter('bar');

        $this->filterSet = new FilterSet('foo filterSet');
        $this->filterSet2 = new FilterSet('bar filterSet'); // no permissions given to this filterset

        $this->questionnaire = new Questionnaire();
        $this->questionnaire->setSurvey($this->survey);
        $this->questionnaire->setDateObservationStart(new \DateTime('2010-01-01T00:00:00+0100'));
        $this->questionnaire->setDateObservationEnd(new \DateTime('2011-01-01T00:00:00+0100'));
        $this->questionnaire->setGeoname($this->geoname);

        $this->question = new NumericQuestion();
        $this->question->setSurvey($this->survey)->setSorting(1)->setFilter($this->filter)->setName('test survey');

        $this->part = new Part();
        $this->part->setName('test part 1');

        $this->part2 = new Part();
        $this->part2->setName('test part 2');

        $this->part3 = new Part();
        $this->part3->setName('test part 3');

        $this->answer = new Answer();
        $this->answer->setQuestion($this->question)->setQuestionnaire($this->questionnaire)->setPart($this->part);

        $this->answer2 = new Answer();
        $this->answer2->setQuestion($this->question)->setQuestionnaire($this->questionnaire)->setPart($this->part2);

        // Get existing roles
        $roleRepository = $this->getEntityManager()->getRepository('Application\Model\Role');
        $editor = $roleRepository->findOneByName('editor');
        $reporter = $roleRepository->findOneByName('reporter');
        $validator = $roleRepository->findOneByName('validator');
        $filterEditor = $roleRepository->findOneByName('Filter editor');

        // Define user as survey editor
        $this->userSurvey = new UserSurvey();
        $this->userSurvey->setUser($this->user)->setSurvey($this->survey)->setRole($editor);

        // Define user as questionnaire reporter (the guy who answer the questionnaire)
        $this->userQuestionnaire1 = new UserQuestionnaire();
        $this->userQuestionnaire1->setUser($this->user)->setQuestionnaire($this->questionnaire)->setRole($reporter);

        // Define user as questionnaire validator (the guy who answer can validate if user is correct)
        $this->userQuestionnaire2 = new UserQuestionnaire();
        $this->userQuestionnaire2->setUser($this->user)->setQuestionnaire($this->questionnaire)->setRole($validator);

        // Define user as "Filter editor" for FilterSet
        $this->userFilterSet = new \Application\Model\UserFilterSet();
        $this->userFilterSet->setUser($this->user)->setFilterSet($this->filterSet)->setRole($filterEditor);

        $this->rule = new Rule();
        $this->rule->setName('test rule')->setFormula('=2 * 3');

        $this->questionnaireUsage = new QuestionnaireUsage();
        $this->questionnaireUsage->setJustification('tests')->setRule($this->rule)->setPart($this->part)->setQuestionnaire($this->questionnaire);

        $this->filterQuestionnaireUsage = new FilterQuestionnaireUsage();
        $this->filterQuestionnaireUsage->setJustification('tests')->setRule($this->rule)->setPart($this->part)->setQuestionnaire($this->questionnaire)->setFilter($this->filter);

        $this->filterGeonameUsage = new FilterGeonameUsage();
        $this->filterGeonameUsage->setJustification('tests')->setRule($this->rule)->setPart($this->part)->setGeoname($this->geoname)->setFilter($this->filter);

        $this->getEntityManager()->persist($this->filterSet);
        $this->getEntityManager()->persist($this->filterSet2);
        $this->getEntityManager()->persist($this->userFilterSet);
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->persist($this->userSurvey);
        $this->getEntityManager()->persist($this->userQuestionnaire1);
        $this->getEntityManager()->persist($this->userQuestionnaire2);
        $this->getEntityManager()->persist($this->part);
        $this->getEntityManager()->persist($this->part2);
        $this->getEntityManager()->persist($this->part3);
        $this->getEntityManager()->persist($this->filter);
        $this->getEntityManager()->persist($this->filter2);
        $this->getEntityManager()->persist($this->geoname);
        $this->getEntityManager()->persist($this->survey);
        $this->getEntityManager()->persist($this->questionnaire);
        $this->getEntityManager()->persist($this->question);
        $this->getEntityManager()->persist($this->answer);
        $this->getEntityManager()->persist($this->answer2);
        $this->getEntityManager()->persist($this->rule);
        $this->getEntityManager()->persist($this->questionnaireUsage);
        $this->getEntityManager()->persist($this->filterQuestionnaireUsage);
        $this->getEntityManager()->persist($this->filterGeonameUsage);
        $this->getEntityManager()->flush();

        // After flushed in DB, we clear EM identiy cache, to be sure that we actually reload object from database
        $this->getEntityManager()->clear();
        $reloadedUser = $this->getEntityManager()->merge($this->user);
        $this->identityProvider->setIdentity($reloadedUser);
    }

    public function testCanGetOne()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);

        $actual = $this->getJsonResponse();
        $allowedFields = $this->getAllowedFields();
        foreach ($actual as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields), "API should not return non-allowed field: '" . $key . "'");
        }

        $this->assertSame($this->getTestedObject()->getId(), $actual['id'], 'should be the same ID that what we asked');
        $this->assertArrayNotHasKey('nonExistingField', $actual);
    }

    public function testCanGetOneWithFields()
    {
        $this->dispatch($this->getRoute('get') . '?fields=metadata,nonExistingField', Request::METHOD_GET);
        $this->assertResponseStatusCode(200);

        $actual = $this->getJsonResponse();
        $this->assertSame($this->getTestedObject()->getId(), $actual['id'], 'should be the same ID that what we asked');
        $this->assertArrayNotHasKey('nonExistingField', $actual, 'unknown fields should be silently ignored');

        $metadata = array(
            'dateCreated',
            'dateModified',
            'creator',
            'modifier',
        );

        foreach ($metadata as $key => $val) {
            $metadata = is_string($key) ? $key : $val;
            $this->assertArrayHasKey($metadata, $actual, 'metadata shortcut should be expanded to common well-known common fields');
        }
    }

    protected function getRoute($method)
    {
        $parts = explode('\\', (get_class($this->getTestedObject())));
        $classname = lcfirst(end($parts));
        $id = $this->getTestedObject()->getId();

        switch ($method) {
            case 'getList':
            case 'post':
                $route = "/api/$classname";
                break;
            case 'get':
            case 'put':
            case 'delete':
                $route = "/api/$classname/$id";
                break;
            default:
                throw new \Exception("Unsupported route '$method' for $classname");
        }

        return $route;
    }

    abstract protected function getAllowedFields();

    abstract protected function getTestedObject();
}
