<?php

namespace ApiTest\Controller;

use Api\Service\MetaModel;
use Application\Model\Answer;
use Application\Model\Filter;
use Application\Model\FilterSet;
use Application\Model\Geoname;
use Application\Model\Part;
use Application\Model\Question\NumericQuestion;
use Application\Model\Questionnaire;
use Application\Model\Survey;
use Application\Model\User;
use Application\Model\UserSurvey;
use Application\Model\UserQuestionnaire;

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
     * @var User
     */
    protected $user;

    /**
     * @var \ZfcRbac\Service\Rbac
     */
    protected $rbac;

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
     * @var Geoname
     */
    protected $geoName;

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

        $this->geoName = new Geoname('test geoname');

        $this->filter = new Filter();
        $this->filter->setName('foo');

        $this->questionnaire = new Questionnaire();
        $this->questionnaire->setSurvey($this->survey);
        $this->questionnaire->setDateObservationStart(new \DateTime('2010-01-01T00:00:00+0100'));
        $this->questionnaire->setDateObservationEnd(new \DateTime('2011-01-01T00:00:00+0100'));
        $this->questionnaire->setGeoname($this->geoName);

        $this->question = new NumericQuestion();
        $this->question->setSurvey($this->survey)
                ->setSorting(1)
                ->setFilter($this->filter)
                ->setName('test survey');

        $this->part = new Part();
        $this->part->setName('test part 1');

        $this->part2 = new Part();
        $this->part2->setName('test part 2');

        $this->part3 = new Part();
        $this->part3->setName('test part 3');

        $this->answer = new Answer();
        $this->answer
                ->setQuestion($this->question)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part);

        $this->answer2 = new Answer();
        $this->answer2
                ->setQuestion($this->question)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part2);

        // create a fake user
        $this->user = new User();
        $this->user->setPassword('foo')->setName('test user');

        // Get rbac service to tell who we are (simulate logged in user)
        $this->rbac = $this->getApplication()->getServiceManager()->get('ZfcRbac\Service\Rbac');
        $this->rbac->setIdentity($this->user);

        // Get existing roles
        $roleRepository = $this->getEntityManager()->getRepository('Application\Model\Role');
        $editor = $roleRepository->findOneByName('editor');
        $reporter = $roleRepository->findOneByName('reporter');
        $validator = $roleRepository->findOneByName('validator');

        // Define user as survey editor
        $this->userSurvey = new UserSurvey();
        $this->userSurvey->setUser($this->user)->setSurvey($this->survey)->setRole($editor);

        // Define user as questionnaire reporter (the guy who answer the questionnaire)
        $this->userQuestionnaire1 = new UserQuestionnaire();
        $this->userQuestionnaire1->setUser($this->user)->setQuestionnaire($this->questionnaire)->setRole($reporter);

        // Define user as questionnaire validator (the guy who answer can validate if user is correct)
        $this->userQuestionnaire2 = new UserQuestionnaire();
        $this->userQuestionnaire2->setUser($this->user)->setQuestionnaire($this->questionnaire)->setRole($validator);

        $this->filterSet = new FilterSet('test filterSet');
        $this->filterSet->addFilter($this->filter);

        $this->getEntityManager()->persist($this->filterSet);
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->persist($this->userSurvey);
        $this->getEntityManager()->persist($this->userQuestionnaire1);
        $this->getEntityManager()->persist($this->userQuestionnaire2);
        $this->getEntityManager()->persist($this->part);
        $this->getEntityManager()->persist($this->part2);
        $this->getEntityManager()->persist($this->part3);
        $this->getEntityManager()->persist($this->filter);
        $this->getEntityManager()->persist($this->geoName);
        $this->getEntityManager()->persist($this->survey);
        $this->getEntityManager()->persist($this->questionnaire);
        $this->getEntityManager()->persist($this->question);
        $this->getEntityManager()->persist($this->answer);
        $this->getEntityManager()->persist($this->answer2);
        $this->getEntityManager()->flush();

        // After flushed in DB, we clear EM identiy cache, to be sure that we actually reload object from database
        $this->getEntityManager()->clear();
        $reloadedUser = $this->getEntityManager()->merge($this->user);
        $this->rbac->setIdentity($reloadedUser);
    }

}
