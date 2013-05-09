<?php

namespace Api\Controller;

use Api\Service\MetaModel;
use Api\Service\Permission;
use Application\Traits\EntityManagerAware;
use Zend\View\Model\JsonModel;
use Application\Model\AbstractModel;

abstract class AbstractRestfulController extends \Zend\Mvc\Controller\AbstractRestfulController
{

    use EntityManagerAware;

    /**
     * @var Permission
     */
    protected $permissionService;

    /**
     * @var MetaModel
     */
    protected $metaModelService;

    /**
     * @var \Application\Service\Hydrator
     */
    protected $hydrator;

    public function __construct()
    {
        $this->permissionService = new Permission($this->getModel());
        $this->metaModelService = new MetaModel();
        $this->hydrator = new \Application\Service\Hydrator();
    }

    /**
     * Must return an array of properties that will be exposed publicly via JSON.
     * If a property is actually an object itself, it must have a sub-array of properties
     *
     * @return array JSON configuration
     */
    protected function getJsonConfig()
    {

        $result = array();
        $fieldList = $this->params()->fromQuery('fields');
        if (!empty($fieldList)) {
            $fields = explode(',', $fieldList);

            // metadata is just an alias to dateModified, dateCreated
            if (in_array('metadata', $fields)) {
                $key = array_search('metadata', $fields);
                unset($fields[$key]);
                $fields = array_merge($fields, $this->metaModelService->getMetadata());
            }
            // Check if fields is allowed to be printed out.
            foreach ($fields as $key => $val) {
                $fieldName = is_string($key) ? $key : $val;
                if ($this->permissionService->isFieldAllowed($fieldName)) {
                    $result[$key] = $val;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the Model class name for the current controller
     *
     * @return string for instance "Application\Model\User"
     */
    protected function getModel()
    {
        $class = get_called_class();
        $shortClass = preg_replace('/(.*\\\\)([^\\\\]+)(Controller$)/', '$2', $class);
        return 'Application\Model\\' . $shortClass;
    }

    /**
     * Returns the repository for the current controller
     *
     * @return \Application\Repository\AbstractRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->getModel());
    }

    /**
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function create($data)
    {
        $modelName = $this->getModel();

        /** @var $object AbstractModel */
        $object = new $modelName();
        $this->hydrator->hydrate($data, $object);


        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();
        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $this->getResponse()->setStatusCode(201);
        return new JsonModel($this->hydrator->extract($object, $this->getJsonConfig()));
    }

    /**
     * @param int $id
     *
     * @return mixed|JsonModel
     */
    public function delete($id)
    {
        $object = $this->getRepository()->findOneBy(array('id' => $id));
        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $this->getEntityManager()->remove($object);
        $this->getEntityManager()->flush();
        $this->getResponse()->setStatusCode(200);
        return new JsonModel(array('message' => 'deleted successfully'));
    }

    /**
     * @param int $id
     *
     * @return mixed|JsonModel
     */
    public function get($id)
    {
        $object = $this->getRepository()->findOneBy(array('id' => $id));
        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new JsonModel($this->hydrator->extract($object, $this->getJsonConfig()));
    }

    /**
     * @return mixed|JsonModel
     */
    public function getList()
    {
        $objects = $this->getRepository()->findAll();

        return new JsonModel($this->hydrator->extractArray($objects, $this->getJsonConfig()));
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        /** @var $object \Application\Model\AbstractModel */
        $object = $this->getRepository()->findOneById($id);

        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
//var_dump($object->getName());
        $this->hydrator->hydrate($data, $object);
        $this->getEntityManager()->flush();
//        var_dump($object->getName());
//die('asdads');
        $this->getResponse()->setStatusCode(201);
        return new JsonModel($this->hydrator->extract($object, $this->getJsonConfig()));
    }

}
