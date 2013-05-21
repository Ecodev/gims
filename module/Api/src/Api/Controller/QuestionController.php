<?php

namespace Api\Controller;

use Application\Model\Survey;
use Application\Module;
use Application\Model\Question;
use Zend\View\Model\JsonModel;

class QuestionController extends AbstractRestfulController
{

    /**
     * @var \Application\Model\Questionnaire
     */
    protected $questionnaire;

    protected function getQuestionnaire()
    {
        $idQuestionnaire = $this->params('idParent');
        if (!$this->questionnaire && $idQuestionnaire) {
            $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
            $this->questionnaire = $questionnaireRepository->find($idQuestionnaire);
        }

        return $this->questionnaire;
    }

    protected function getJsonConfig()
    {
        $questionnaire = $this->getQuestionnaire();
        $controller = $this;
        return array(
            'name',
            'sorting',
            'filter' => array(
                'name',
                'parents' => array(
                    'name',
                    'parents' => array(
                        'name',
                    ),
                ),
            ),
            // Here we use a closure to get the questions' answers, but only for the current questionnaire
            'answers' => function(\Application\Service\Hydrator $hydrator, Question $question) use($questionnaire, $controller) {
                $answerRepository = $controller->getEntityManager()->getRepository('Application\Model\Answer');
                $answers = $answerRepository->findBy(array(
                    'question' => $question,
                    'questionnaire' => $questionnaire,
                ));

                $answers = $hydrator->extractArray($answers, array(
                            'valuePercent',
                            'valueAbsolute',
                            'questionnaire' => array(),
                            'part' => array(
                                'name',
                            )
                ));

                // special case for question, reorganize keys for the needs of NgGrid:
                // Numerical key must correspond to the id of the part.
                $output = array();
                foreach ($answers as $answer) {

                    if (! empty($answer['part']['id'])) {
                        $output[$answer['part']['id']] = $answer;
                    } else {
                        // It is ok to have one answer in position 0 (= total) but not more!
                        // should not be the case... so log it
                        if (! empty($output[0])) {
                            $logger = Module::getServiceManager()->get('Zend\Log');
                            $logger->info(sprintf('[WARNING] Answer object "%s" has too many null Part. ', $answer['id']));
                        }
                        $output[0] = $answer;
                    }
                }
                return $output;
            }
        );
    }

    public function getList()
    {
        $questionnaire = $this->getQuestionnaire();

        // Cannot list all question, without specifying a questionnaire
        if (!$questionnaire) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $questions = $this->getRepository()->findBy(array(
            'survey' => $questionnaire->getSurvey(),
        ));

        return new JsonModel($this->hydrator->extractArray($questions, $this->getJsonConfig()));
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        // Retrieve question since permissions apply against it.
        /** @var $question \Application\Model\Question */
        $questionRepository = $this->getEntityManager()->getRepository($this->getModel());
        $question = $questionRepository->findOneById($id);
        $survey = $question->getSurvey();

        // Update object or not...
        if ($this->isAllowedSurvey($survey) && $this->isAllowedQuestion($question)) {

            // true means we have to move sorting values up and down
            if (!empty($data['sorting']) && $data['sorting'] < $question->getSorting()) {

                foreach ($survey->getQuestions() as $_question) {
                    if ($_question->getSorting() >= $data['sorting'] && $_question->getSorting() < $question->getSorting()) {
                        $_question->setSorting($_question->getSorting() + 1);
                    }
                }
            } elseif (!empty($data['sorting']) && $data['sorting'] > $question->getSorting()) {
                foreach ($survey->getQuestions() as $_question) {
                    if ($_question->getSorting() <= $data['sorting'] && $_question->getSorting() > $question->getSorting()) {
                        $_question->setSorting($_question->getSorting() - 1);
                    }
                }
            }
            $result = parent::update($id, $data);
        } else {
            $this->getResponse()->setStatusCode(401);
            $result = new JsonModel(array('message' => 'Authorization required'));
        }
        return $result;
    }

    /**
     * @param array $data
     *
     * @return mixed|void|JsonModel
     * @throws \Exception
     */
    public function create($data)
    {
        // Get the last sorting value from question
        if (empty($data['survey']) && (int) $data['survey'] > 0) {
            throw new \Exception('Missing or invalid survey value', 1368459230);
        }

        // Retrieve a questionnaire from the storage
        $repository = $this->getEntityManager()->getRepository('Application\Model\Survey');
        /** @var Survey $survey */
        $survey = $repository->findOneById((int) $data['survey']);

        /** @var \Doctrine\Common\Collections\ArrayCollection $questions */
        $questions = $survey->getQuestions();

        if ($questions->isEmpty()) {
            $data['sorting'] = 1;
        } else {
            /** @var Question $question */
            $question = $questions->last();
            $data['sorting'] = $question->getSorting() + 1;
        }

        // Check that all required properties are given by the GUI
        $properties = $this->metaModelService->getMandatoryProperties();
        foreach ($data as $propertyName => $value) {
            if (! in_array($propertyName, $properties)) {
                throw new \Exception('Missing property ' . $propertyName, 1368459231);
            }
        }

        // Update object or not...
        if ($this->isAllowedSurvey($survey)) {
            $result = parent::create($data);
        } else {
            $this->getResponse()->setStatusCode(401);
            $result = new JsonModel(array('message' => 'Authorization required'));
        }
        return $result;
    }

    /**
     * Ask Rbac whether the User is allowed to update this survey
     *
     * @param Survey $survey
     * @return bool
     */
    protected function isAllowedSurvey(Survey $survey)
    {

        // @todo remove me once login will be better handled GUI wise
        return true;

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        return $rbac->isGrantedWithContext(
            $survey,
            Permission::CAN_MANAGE_SURVEY,
            new SurveyAssertion($survey)
        );
    }

    /**
     * Ask Rbac whether the User is allowed to update this survey
     *
     * @param Question $question
     *
     * @return bool
     */
    protected function isAllowedQuestion(Question $question)
    {

        // @todo remove me once login will be better handled GUI wise
        return true;

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        return $rbac->isGrantedWithContext(
            $question,
            Permission::CAN_CREATE_OR_UPDATE_QUESTION,
            new QuestionAssertion($question)
        );
    }
}
