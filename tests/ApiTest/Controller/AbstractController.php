<?php

namespace ApiTest\Controller;

use Api\Service\MetaModel;
use Application\Model\Answer;
use Application\Model\Filter;
use Application\Model\Geoname;
use Application\Model\Part;
use Application\Model\Permission;
use Application\Model\Question;
use Application\Model\Questionnaire;
use Application\Model\Role;
use Application\Model\Survey;
use Application\Model\User;
use Application\Model\UserQuestionnaire;
use Zend\Json\Json;

abstract class AbstractController extends \ApplicationTest\Controller\AbstractController
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
     * @var Question
     */
    protected $question;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Part
     */
    protected $part;

    /**
     * @var Part
     */
    protected $part2;

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
     * @var User
     */
    protected $user;

    /**
     * @var \ZfcRbac\Service\Rbac
     */
    protected $rbac;

    /**
     * @var Permission
     */
    protected $permission;

    /**
     * @var UserQuestionnaire
     */
    protected $userQuestionnaire;

    /**
     * @var Role
     */
    protected $role;

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
        $this->survey->setActive(true);
        $this->survey->setName('test survey');
        $this->survey->setCode('code test survey');
        $this->survey->setYear(2010);

        $this->geoName = new Geoname('test geoname');

        $this->filter = new Filter();
        $this->filter->setName('foo')
                ->setIsOfficial(true);

        $this->questionnaire = new Questionnaire();
        $this->questionnaire->setSurvey($this->survey);
        $this->questionnaire->setDateObservationStart(new \DateTime('2010-01-01T00:00:00+0100'));
        $this->questionnaire->setDateObservationEnd(new \DateTime('2011-01-01T00:00:00+0100'));
        $this->questionnaire->setGeoname($this->geoName);

        $this->question = new Question();
        $this->question->setSurvey($this->survey)
                ->setSorting(1)
                ->setType(1)
                ->setFilter($this->filter)
                ->setName('foo');
        $this->part = new Part();
        $this->part->setName('test part 1');

        $this->part2 = new Part();
        $this->part2->setName('test part 2');

        $this->answer = new Answer();
        $this->answer
                ->setQuestion($this->question)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part); // answer one has a part whereas question two not.

        $this->answer2 = new Answer();
        $this->answer2
                ->setQuestion($this->question)
                ->setQuestionnaire($this->questionnaire);



        // create a fake user
        $this->user = new User();
        $this->user->setPassword('foo')->setName('test user');

        // Get rbac service
        $this->rbac = $this->getApplication()->getServiceManager()->get('ZfcRbac\Service\Rbac');

        // Get existing permission
        $repository = $this->getEntityManager()->getRepository('Application\Model\Permission');

        /** @var $role \Application\Model\Permission */
        $this->permission = $repository->findOneByName(\Application\Model\Permission::CAN_CREATE_OR_UPDATE_ANSWER);

        $this->role = new Role('foo');
        $this->role->addPermission($this->permission);

        // create a fake user-questionnaire
        $this->userQuestionnaire = new UserQuestionnaire();
        $this->userQuestionnaire->setUser($this->user)->setQuestionnaire($this->questionnaire)->setRole($this->role);

        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->persist($this->role);
        $this->getEntityManager()->persist($this->userQuestionnaire);
        $this->getEntityManager()->persist($this->part);
        $this->getEntityManager()->persist($this->part2);
        $this->getEntityManager()->persist($this->filter);
        $this->getEntityManager()->persist($this->geoName);
        $this->getEntityManager()->persist($this->survey);
        $this->getEntityManager()->persist($this->questionnaire);
        $this->getEntityManager()->persist($this->question);
        $this->getEntityManager()->persist($this->answer);
        $this->getEntityManager()->persist($this->answer2);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array
     */
    protected function getJsonResponse()
    {
        $content = $this->getResponse()->getContent();
        try {
            $json = Json::decode($content, Json::TYPE_ARRAY);
        } catch (\Zend\Json\Exception\RuntimeException $exception) {
            throw new \Zend\Json\Exception\RuntimeException($exception->getMessage() . PHP_EOL . PHP_EOL . $content . PHP_EOL, $exception->getCode(), $exception);
        }

        $this->assertTrue(is_array($json));
        return $json;
    }

}
