<?php

namespace Api\Controller;

use Application\Model\AbstractModel;
use Zend\View\Model\JsonModel;

class SurveyController extends AbstractRestfulController
{

    /**
     * Give editor role to the user on the new survey, so he can create questions and so on
     * @param \Application\Model\AbstractModel $survey
     */
    protected function postCreate(AbstractModel $survey, array $data)
    {
        $user = $this->getAuth()->getIdentity();
        $role = $this->getEntityManager()->getRepository(\Application\Model\Role::class)->findOneByName('Survey editor');
        $userSurvey = new \Application\Model\UserSurvey();
        $userSurvey->setUser($user)->setSurvey($survey)->setRole($role);

        $this->getEntityManager()->persist($userSurvey);
        $this->getEntityManager()->flush();
    }

    /**
     * @return JsonModel
     */
    public function getList()
    {
        $surveyTypes = $this->getSurveyTypes();
        $objects = $this->getRepository()->getAllWithPermission($this->params()->fromQuery('permission', 'read'), $this->params()->fromQuery('q'), $surveyTypes);
        $jsonData = $this->paginate($objects);

        return new JsonModel($jsonData);
    }
}
