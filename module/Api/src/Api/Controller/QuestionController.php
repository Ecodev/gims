<?php

namespace Api\Controller;

use Application\Model\Survey;
use Application\Module;
use Application\Model\Question;
use Application\Model\Choice;
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
        if(!$this->questionnaire && $idQuestionnaire) {
            $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
            $this->questionnaire = $questionnaireRepository->find($idQuestionnaire);
        }

        return $this->questionnaire;
    }

    protected function getClosures()
    {
        $questionnaire = $this->getQuestionnaire();
        $controller = $this;
        $config = array(
            // @todo remove it has been proven to work
            // Here we use a closure to get the questions' answers, but only for the current questionnaire
            'answers' => function (\Application\Service\Hydrator $hydrator, Question $question) use (
                $questionnaire, $controller
            ) {
                $answerRepository = $controller->getEntityManager()->getRepository('Application\Model\Answer');
                $answers = $answerRepository->findBy(
                    array(
                        'question'      => $question,
                        'questionnaire' => $questionnaire,
                    )
                );

                // special case for question, reorganize keys for the needs of NgGrid:
                // Numerical key must correspond to the id of the part.
                $output = array();
                foreach ($answers as $answer) {
                    $part = $answer->getPart();
                    $answerData = $hydrator->extract($answer, \Application\Model\Answer::getJsonConfig());
                    $answerData['part'] = $hydrator->extract($part, \Application\Model\Part::getJsonConfig());

                    if ($part->isTotal())
                        $index = 0;
                    else
                        $index = $part->getId();

                    $output[$index] = $answerData;
                }
                return $output;
            }
        );

        return $config;
    }

    public function getList()
    {

        //        $questionRepository = $this->getEntityManager()->getRepository('Application\Model\Question');
        //        $questions = $questionRepository->findAll();
        //
        //        /** @var Question $question */
        //        foreach ($questions as $question) {
        //            $newName = $question->getFilter()->getName();
        //            $question->setName($newName);
        //            $this->getEntityManager()->persist($question);
        //        }
        //            $this->getEntityManager()->flush();

        $questionnaire = $this->getQuestionnaire();

        // Cannot list all question, without specifying a questionnaire
        if(!$questionnaire) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $questions = $this->getRepository()->findBy(
            array(
                'survey' => $questionnaire->getSurvey(),
            ),
            array(
                'sorting' => 'ASC'
            )
        );

        //$questions = $this->hydrator->extractArray($questions, $this->getJsonConfig());

        $flatQuestions = array();
        foreach($questions as $key => $question){
            $flatQuestion = array('id' => $question->getId(),
                                  'name' => $question->getName());
            if(!is_null($question->getParent())) $flatQuestion['parentid'] = $question->getParent()->getId();
            else $flatQuestion['parentid'] = 0;
            array_push($flatQuestions, $flatQuestion);
        }

        $new = array();
        foreach ($flatQuestions as $a)
            $new[$a['parentid']][] = $a;

        $questions = $this->createTree($new, $new[0], 0);

        return new JsonModel($questions);
    }


    protected function createTree(&$list, $parent, $deep){
        $tree = array();
        foreach ($parent as $k=>$l){
            $l['level'] = $deep;
            if(isset($list[$l['id']])){
                $children = $this->createTree($list, $list[$l['id']], $deep+1);
                $tree[] = $l;
                $tree = array_merge($tree, $children);
            }else{
                $tree[] = $l;
            }

        }
        return $tree;
    }





    /**
     * @param int $id
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
        if($this->isAllowedSurvey($survey) && $this->isAllowedQuestion($question)) {

            // true means we have to move sorting values up and down
            if(!empty($data['sorting']) && $data['sorting'] < $question->getSorting()) {

                foreach ($survey->getQuestions() as $_question) {
                    if($_question->getSorting() >= $data['sorting']
                        && $_question->getSorting() < $question->getSorting()
                    ) {
                        $_question->setSorting($_question->getSorting() + 1);
                    }
                }
            } elseif(!empty($data['sorting']) && $data['sorting'] > $question->getSorting()) {
                foreach ($survey->getQuestions() as $_question) {
                    if($_question->getSorting() <= $data['sorting']
                        && $_question->getSorting() > $question->getSorting()
                    ) {
                        $_question->setSorting($_question->getSorting() - 1);
                    }
                }
            }


            if(isset($data['choices'])) {
                $this->setChoices($data['choices'], $question);
                unset($data['choices']);
            }


            $result = parent::update($id, $data);
        } else {
            $this->getResponse()->setStatusCode(401);
            $result = new JsonModel(array('message' => 'Authorization required'));
        }
        return $result;
    }



    protected function setChoices(array $newChoices, $question)
    {
        $i = 0;
        foreach ($newChoices as $key => $newChoice) {
            $newChoice['sorting'] = $i;
            // if no id -> create
            if(!isset($newChoice['id'])) {
                $choice = new Choice();
                $this->getEntityManager()->persist($choice);
            } // if id exists -> update
            else {
                $choiceRepository = $this->getEntityManager()->getRepository('Application\Model\Choice');
                $choice = $choiceRepository->findOneById((int)$newChoice['id']);
            }
            $choice->setQuestion($question);
            $this->hydrator->hydrate($newChoice, $choice);
            $i++;
        }

        // no way to detect removed choices by looping on $newChoices
        // its necessary to loop on $actualChoices and detect which are no more in $newChoices
        $actualChoices = $question->getChoices();
        if(sizeof($actualChoices) > 0) {
            foreach ($actualChoices as $choice) {
                $exist = false;
                foreach ($newChoices as $newChoice) {
                    if($newChoice['id'] == $choice->getId()) {

                        $exist = true;
                        break;
                    }
                }


                if(!$exist) {

                    $question->getChoices()->removeElement($choice);
                    \Application\Module::getEntityManager()->remove($choice);
                }
            }
        }

    }



    /**
     * @param array $data
     *
     * @param callable $postAction
     * @throws \Exception
     * @return mixed|void|JsonModel
     */
    public function create($data, \Closure $postAction=null)
    {
        // Get the last sorting value from question
        if(empty($data['survey']) && (int)$data['survey'] > 0) {
            throw new \Exception('Missing or invalid survey value', 1368459230);
        }

        // Retrieve a questionnaire from the storage
        $repository = $this->getEntityManager()->getRepository('Application\Model\Survey');
        /** @var Survey $survey */
        $survey = $repository->findOneById((int)$data['survey']);

        /** @var \Doctrine\Common\Collections\ArrayCollection $questions */
        $questions = $survey->getQuestions();

        if($questions->isEmpty()) {
            $data['sorting'] = 1;
        } else {
            /** @var Question $question */
            $question = $questions->last();
            $data['sorting'] = $question->getSorting() + 1;
        }

        // Check that all required properties are given by the GUI
        $properties = $this->metaModelService->getMandatoryProperties();
        $dataKeys = array_keys($data);

        foreach ($properties as $propertyName) {
            if(!in_array($propertyName, $dataKeys)) {
                throw new \Exception('Missing property ' . $propertyName, 1368459231);
            }
        }

        // Update object or not...

        if($this->isAllowedSurvey($survey)) {

            // unset ['choices'] to preserve $data from hydrator and backup in a variable
            $newChoices = null;
            if(isset($data['choices']))
            {
                $newChoices = $data['choices'];
                unset($data['choices']);
            }
            $self = $this;
            $result = parent::create($data, function (\Application\Model\Question $question)
                                            use ($newChoices, $self) {
                                                if($newChoices)
                                                $self->setChoices($newChoices, $question);
                                         });


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
     *
     * @return bool
     */
    protected function isAllowedSurvey(Survey $survey)
    {

        // @todo remove me once login will be better handled GUI wise
        return true;

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        return $rbac->isGrantedWithContext(
            $survey, Permission::CAN_MANAGE_SURVEY, new SurveyAssertion($survey)
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
            $question, Permission::CAN_CREATE_OR_UPDATE_QUESTION, new QuestionAssertion($question)
        );
    }

}
