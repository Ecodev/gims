<?php

namespace Api\Controller;

use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Application\Model\AbstractModel;
use Application\Model\Survey;
use Application\Model\Question\AbstractQuestion;
use Application\Model\Question\Choice;

class QuestionController extends AbstractChildRestfulController
{

    use \Application\Traits\FlatHierarchicQuestions;

    /**
     * The new choices to be added to the newly created question
     * @var array|null
     */
    private $newChoices = null;

    protected function getClosures()
    {
        $questionnaire = $this->getParent();
        $controller = $this;
        $config = array(
            // @todo remove it has been proven to work
            // Here we use a closure to get the questions' answers, but only for the current questionnaire

            'answers' => function (\Application\Service\Hydrator $hydrator, AbstractQuestion $question) use (
                $questionnaire, $controller
            ) {
                $answerRepository = $controller->getEntityManager()->getRepository('Application\Model\Answer');
                $answers = $answerRepository->findBy(array(
                    'question'      => $question,
                    'questionnaire' => $questionnaire
                ));

                // special case for question, reorganize keys for the needs of NgGrid:
                // Numerical key must correspond to the id of the part.
                $output = array();
                foreach ($answers as $answer) {

                    // If does not have access to answer, skip silently
                    if (!$controller->getRbac()->isActionGranted($answer, 'read')) {
                        continue;
                    }

                    $part = $answer->getPart();
                    $answerData = $hydrator->extract($answer);
                    $answerData['part'] = $hydrator->extract($part);

                    array_push($output, $answerData);
                }

                return $output;
            },
            //
            //            'permissions' => function (\Application\Service\Hydrator $hydrator, AbstractQuestion $question) use (
            //                $questionnaire, $controller
            //            ) {
            //                return $questionnaire->getPermissions();
            //            }
        );

        return $config;
    }

    /**
     * In the special case of QuestionController we want to be able to
     * create/update several concrete class. So we accept a special 'type'
     * parameter to let us know what type we actuall want to.
     * @return string
     */
    protected function getModel()
    {
        $request = $this->getRequest();
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)
        ) {
            $data = Json::decode($request->getContent(), $this->jsonDecodeType);
        } else {
            $data = $request->getPost()->toArray();
        }

        if (isset($data['type'])) {
            $type = \Application\Model\QuestionType::get($data['type']);
            $class = \Application\Model\QuestionType::getClass($type);

            return $class;
        }

        return '\Application\Model\Question\AbstractQuestion';
    }

    public function getList()
    {

        $parent = $this->getParent();
        $permission = $this->params()->fromQuery('permission', 'read');
        if ($parent instanceof \Application\Model\Question\Chapter) {
            $questions = $this->getRepository()->getAllWithPermission($permission, 'chapter', $parent);
        } elseif ($parent instanceof \Application\Model\Survey) {
            $questions = $this->getRepository()->getAllWithPermission($permission, 'survey', $parent);
        } elseif ($parent instanceof \Application\Model\Questionnaire) {
            $questions = $this->getRepository()->getAllWithPermission($permission, 'survey', $parent->getSurvey());
            // Cannot list all question, without specifying a questionnaire, survey or chapter
        } else {
            $this->getResponse()->setStatusCode(400);

            return new JsonModel(array('message' => 'Cannot list all items without a valid parent. Use URL similar to: /api/parent/1/question'));
        }
        $questions = $this->getFlatHierarchy($questions, $this->getJsonConfig(), $this->hydrator);

        return new JsonModel($questions);
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        /** @var $question \Application\Model\Question\AbstractQuestion */
        $questionRepository = $this->getRepository();

        $this->getEntityManager()->getConnection()->beginTransaction(); // suspend auto-commit
        if (isset($data['type'])) {
            $questionRepository->changeType($id, \Application\Model\QuestionType::get($data['type']));
        }

        // If not allowed to read the object, cancel everything
        $question = $questionRepository->findOneById($id);
        if ($this->getRbac()->isActionGranted($question, 'update')) {
            $this->getEntityManager()->getConnection()->commit();
        } else {
            $this->getEntityManager()->getConnection()->rollback();
            $this->getResponse()->setStatusCode(403);

            return new JsonModel(array('message' => $this->getRbac()->getMessage()));
        }

        if (isset($data['sorting'])) {
            $data['sorting'] = $this->reorderSiblingQuestions($question, $data['sorting']);
        }

        if (isset($data['choices'])) {
            $this->setChoices($data['choices'], $question);
            unset($data['choices']);
        }
        return parent::update($id, $data);
    }

    /**
     * Reorder sibling questions to make room for the new question according to its sorting
     *
     * @param \Application\Model\Question\AbstractQuestion $question the question BEFORE setting its new sorting
     * @param integer $newSorting                                    the new sorting
     */
    protected function reorderSiblingQuestions(AbstractQuestion $question, $newSorting)
    {
        $survey = $question->getSurvey();
        $questionSiblings = $question->getChapter() ? $question->getChapter()->getQuestions() : $survey->getQuestions();
        $lastSibling = $questionSiblings->last();
        $firstSibling = $questionSiblings->first();

        // limit to next available sorting free number
        if ($newSorting > $lastSibling->getSorting() + 1) {
            $newSorting = $lastSibling->getSorting() + 1;

            // limit to previous available sorting free number
        } elseif ($newSorting < $firstSibling->getSorting() - 1) {
            $newSorting = $firstSibling->getSorting() - 1;

            // true means we have to move sorting values up and down
        } elseif ($newSorting < $question->getSorting()) { // if new sorting is lower
            foreach ($questionSiblings as $questionSibling) {
                if ($questionSibling->getSorting() >= $newSorting && $questionSibling->getSorting() < $question->getSorting()) {
                    $questionSibling->setSorting($questionSibling->getSorting() + 1);
                }
            }
        } elseif ($newSorting > $question->getSorting()) { // if new sorting is higher
            foreach ($questionSiblings as $questionSibling) {
                if ($questionSibling->getSorting() <= $newSorting && $questionSibling->getSorting() > $question->getSorting()) {
                    $questionSibling->setSorting($questionSibling->getSorting() - 1);
                }
            }
        }

        return $newSorting;
    }

    /**
     * Affects the new choices to the given question, and remove the obsolete choices
     *
     * @param array $newChoices
     * @param \Application\Model\Question\AbstractQuestion $question
     */
    protected function setChoices(array $newChoices, AbstractQuestion $question)
    {
        $i = 0;
        $newChoicesObjects = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($newChoices as $newChoice) {

            // If choice is incomplete, ignore it
            if (!isset($newChoice['name'])) {
                continue;
            }
            $newChoice['sorting'] = $i;
            // if no id -> create
            if (!isset($newChoice['id'])) {
                $choice = new Choice();
                $this->getEntityManager()->persist($choice);
            } // if id exists -> update
            else {
                $choiceRepository = $this->getEntityManager()->getRepository('Application\Model\Question\Choice');
                $choice = $choiceRepository->findOneById((int) $newChoice['id']);
            }
            $this->hydrator->hydrate($newChoice, $choice);
            $newChoicesObjects->add($choice);
            $i++;
        }

        $question->setChoices($newChoicesObjects);
    }

    /**
     * Create choices for the newly created question
     *
     * @param \Application\Model\AbstractModel $question
     */
    protected function postCreate(AbstractModel $question, array $data)
    {
        if ($question instanceof \Application\Model\Question\ChoiceQuestion && $this->newChoices) {
            $this->setChoices($this->newChoices, $question);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array $data
     * @param callable $postAction
     *
     * @throws \Exception
     * @return mixed|void|JsonModel
     */
    public function create($data)
    {
        $repository = $this->getEntityManager()->getRepository('Application\Model\Survey');
        /** @var Survey $survey */
        $survey = $repository->findOneById((int) @$data['survey']);

        // Check that we have a survey
        if (!$survey) {
            throw new \Exception('Missing or invalid survey value', 1368459230);
        }

        /** @var \Doctrine\Common\Collections\ArrayCollection $questions */
        $questions = $survey->getQuestions();

        if ($questions->isEmpty()) {
            $data['sorting'] = 1;
        } else {

            // get the last sibling to get last sorting number
            $questions = $survey->getQuestions();
            if (isset($data['chapter'])) {
                foreach ($questions as $question) {
                    if ($question->getId() == $data['chapter']) {
                        $question = $question->getQuestions()->last();
                        break;
                    }
                }
            } else {
                $question = $survey->getQuestions()->last();
            }

            if ($question) {
                $data['sorting'] = $question->getSorting() + 1;
            } else {
                $data['sorting'] = 1;
            }
        }

        // unset ['choices'] to preserve $data from hydrator and backup in a variable for use in postCreate()
        if (isset($data['choices'])) {
            $this->newChoices = $data['choices'];
            unset($data['choices']);
        }

        return parent::create($data);
    }

}