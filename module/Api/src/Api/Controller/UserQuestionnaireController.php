<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class UserQuestionnaireController extends AbstractRestfulController
{

    /**
     * @var \Application\Model\AbstractModel
     */
    protected $parent;

    /**
     * Get the parent, either User, Survey, or Role
     * @return \Application\Model\AbstractModel
     */
    protected function getParent()
    {
        $id = $this->params('idParent');
        if (!$this->parent && $id) {
            $userRepository = $this->getEntityManager()->getRepository('Application\Model\\' . ucfirst($this->params('parent')));
            $this->parent = $userRepository->find($id);
        }

        return $this->parent;
    }

    public function getList()
    {
        $parent = $this->getParent();

        // Cannot list all userQuestionnaires, without a parent
        if (!$parent) {
            $this->getResponse()->setStatusCode(404);

            return;
        }

        $userQuestionnaires = $this->getRepository()->findBy(array($this->params('parent') => $parent));

        return new JsonModel($this->hydrator->extractArray($userQuestionnaires, $this->getJsonConfig()));
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        throw new \Exception('Not implemented');
    }

}
