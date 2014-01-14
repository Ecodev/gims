<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\Question\NumericQuestion;
use Application\Model\Geoname;
use Application\Model\Answer;
use Application\Model\Questionnaire;
use Application\Model\FilterSet;
use Application\Model\Filter;
use Application\Model\Survey;
use Application\Model\User;
use Application\Model\UserSurvey;
use Application\Model\UserQuestionnaire;
use Application\Model\Role;

class ChartFilterGeneratorController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        $name = $this->params()->fromQuery('name');
        $surveys = explode(',', $this->params()->fromQuery('surveys'));

        $existingSurvey = $this->getEntityManager()->getRepository('Application\Model\Survey')->findOneByName($name);
        $existingFilter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneByName($name);
        $existingFilterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneByName($name);

        if ($existingSurvey || $existingFilter || $existingFilterSet) {
            return new NumericJsonModel(array('message' => 'name "' . $name . '" already used'));
        }

        $user = User::getCurrentUser();

        /** get roles */
        $roleEditor = $this->getEntityManager()->getRepository('Application\Model\Role')->findOneByName('editor');
        $roleReporter = $this->getEntityManager()->getRepository('Application\Model\Role')->findOneByName('reporter');

        /** @var \Application\Model\Part $part */
        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));
        $parts = new ArrayCollection();
        $parts->add($part);

        /** @var \Application\Model\Country $country */
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneById($this->params()->fromQuery('country'));

        /** @var \Application\Model\Geoname $geoname */
        $geoname = $country->getGeoname();

        /** @var \Application\Model\FilterSet $filterSet */
        $filterSet = new FilterSet();
        $filterSet->setName($name);
        $this->getEntityManager()->persist($filterSet);

        /** @var \Application\Model\Filter $filter */
        $filter = new Filter();
        $filter->setName($name);
        $filterSet->addFilter($filter);
        $this->getEntityManager()->persist($filter);

        foreach ($surveys as $s) {

            list($year, $value) = explode(':', $s);

            /** @var \Application\Model\Survey $survey */
            $survey = new Survey();
            $survey->setCode($name . ' ' . $year);
            $survey->setName($name . ' ' . $year);
            $survey->setYear($year);
            $survey->setIsActive(1);
            $this->getEntityManager()->persist($survey);

            /** @var \Application\Model\UserSurvey $userSurvey */
            $userSurvey = new UserSurvey();
            $userSurvey->setUser($user);
            $userSurvey->setSurvey($survey);
            $userSurvey->setRole($roleEditor);
            $this->getEntityManager()->persist($userSurvey);

            /** @var \Application\Model\NumericQuestion $question */
            $question = new NumericQuestion();
            $question->setName($name);
            $question->setFilter($filter);
            $question->setSurvey($survey);
            $question->setParts($parts);
            $question->setSorting(1);
            $question->setIsPopulation(true);
            $question->setIsCompulsory(true);
            $this->getEntityManager()->persist($question);

            /** @var \Application\Model\Questionnaire $questionnaire */
            $questionnaire = new Questionnaire();
            $questionnaire->setSurvey($survey);
            $questionnaire->setGeoname($geoname);
            $questionnaire->setDateObservationStart(new \DateTime($year . '-01-01'));
            $questionnaire->setDateObservationEnd(new \DateTime($year . '-12-31'));
            $this->getEntityManager()->persist($questionnaire);

            /** @var \Application\Model\UserQuestionnaire $userQuestionnaire */
            $userQuestionnaire = new UserQuestionnaire();
            $userQuestionnaire->setUser($user);
            $userQuestionnaire->setQuestionnaire($questionnaire);
            $userQuestionnaire->setRole($roleEditor);
            $this->getEntityManager()->persist($userQuestionnaire);
            $this->getEntityManager()->persist($questionnaire);

            /** @var \Application\Model\UserQuestionnaire $userQuestionnaire */
            $userQuestionnaire = new UserQuestionnaire();
            $userQuestionnaire->setUser($user);
            $userQuestionnaire->setQuestionnaire($questionnaire);
            $userQuestionnaire->setRole($roleReporter);
            $this->getEntityManager()->persist($userQuestionnaire);

            /** @var \Application\Model\Population $population */
            $population = $this->getEntityManager()->getRepository('Application\Model\Population')->getOneByGeoname($geoname, $part->getId(), $year);

            /** @var \Application\Model\Answer $answer */
            $answer = new Answer();
            $answer->setQuestion($question);
            $answer->setQuestionnaire($questionnaire);
            $answer->setPart($part);
            $answer->setValuePercent($value);
            $answer->setValueAbsolute($value * $population->getPopulation());
            $this->getEntityManager()->persist($answer);
        }

        $this->getEntityManager()->flush();
        $this->getResponse()->setStatusCode(201);

        $hydrator = new \Application\Service\Hydrator();

        return new NumericJsonModel($hydrator->extract($filterSet));
    }
}
