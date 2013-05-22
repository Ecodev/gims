<?php

namespace Api\Controller;

use Application\Model\Questionnaire;
use Zend\View\Model\JsonModel;

class QuestionnaireController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        $controller = $this;

        return array(
            'name',
            'dateObservationStart',
            'dateObservationEnd',
            'validatorNames',
            'reporterNames',
            'completed',
            'spatial',
            'dateLastAnswerModification' => function(\Application\Service\Hydrator $hydrator, Questionnaire $questionnaire) use($controller) {
                $answerRepository = $controller->getEntityManager()->getRepository('Application\Model\Answer');
                $criteria = array(
                    'questionnaire' => $questionnaire->getId(),
                );
                $order = array(
                    'dateModified' => 'DESC',
                );
                /** @var \Application\Model\Answer $answer */
                $answer = $answerRepository->findOneBy($criteria, $order);
                return $answer->getDateModified() === null ?
                    $answer->getDateCreated()->format(DATE_ISO8601) :
                    $answer->getDateModified()->format(DATE_ISO8601);
            },
            'reporterNames' => function(\Application\Service\Hydrator $hydrator, Questionnaire $questionnaire) use($controller) {
                $roleRepository = $controller->getEntityManager()->getRepository('Application\Model\Role');

                // @todo find a way making sure we have a role reporter
                /** @var \Application\Model\Role $role */
                $role = $roleRepository->findOneByName('reporter');

                $userQuestionnaireRepository = $controller->getEntityManager()->getRepository('Application\Model\UserQuestionnaire');
                $criteria = array(
                    'questionnaire' => $questionnaire->getId(),
                    'role' => $role->getId(),
                );

                $results = array();
                /** @var \Application\Model\UserQuestionnaire $userQuestionnaire */
                foreach ($userQuestionnaireRepository->findBy($criteria) as $userQuestionnaire) {
                    $results[] = $userQuestionnaire->getUser()->getName();
                }

                return implode(',', $results);
            },
            'validatorNames' => function(\Application\Service\Hydrator $hydrator, Questionnaire $questionnaire) use($controller) {
                $roleRepository = $controller->getEntityManager()->getRepository('Application\Model\Role');

                // @todo find a way making sure we have a role reporter
                /** @var \Application\Model\Role $role */
                $role = $roleRepository->findOneByName('validator');

                $userQuestionnaireRepository = $controller->getEntityManager()->getRepository('Application\Model\UserQuestionnaire');
                $criteria = array(
                    'questionnaire' => $questionnaire->getId(),
                    'role' => $role->getId(),
                );

                $results = array();
                /** @var \Application\Model\UserQuestionnaire $userQuestionnaire */
                foreach ($userQuestionnaireRepository->findBy($criteria) as $userQuestionnaire) {
                    $results[] = $userQuestionnaire->getUser()->getName();
                }

                return implode(',', $results);
            },
            'survey' => array(
                'code',
                'name'
            ),
            'geoname' => array('name'),
        );
    }
}
