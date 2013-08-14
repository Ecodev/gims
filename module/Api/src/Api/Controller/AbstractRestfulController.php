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
    private $permissionService;

    /**
     * @var MetaModel
     */
    private $metaModelService;

    /**
     * @var \Application\Service\Hydrator
     */
    protected $hydrator;

    /**
     * Returns permission service
     * @return \Api\Service\Permission
     */
    protected function getPermissionService()
    {
        if (!$this->permissionService) {
            $this->permissionService = new Permission($this->getModel());
        }

        return $this->permissionService;
    }

    /**
     * Returns MetaModel service
     * @return \Api\Service\MetaModel
     */
    protected function getMetaModelService()
    {
        if (!$this->metaModelService) {
            $this->metaModelService = new MetaModel($this->getModel());
        }

        return $this->metaModelService;
    }

    public function __construct()
    {
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
            $result = explode(',', $fieldList);
        }

        // Overwrite existing properties with closures, if any
        $closure = $this->getClosures();
        foreach ($result as $key => $property) {
            if (isset($closure[$property])) {
                unset($result[$key]);
                $result[$property] = $closure[$property];
            }
        }

        return $result;
    }

    /**
     * Optionnaly return closures to override json properties
     * @return array
     */
    protected function getClosures()
    {
        return array();
    }

    /**
     * Returns the Model class name for the current controller
     *
     * @return string for instance "Application\Model\User"
     */
    protected function getModel(array $data = null)
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
     * @param callable $postAction
     * @return mixed|JsonModel
     */
    public function create($data, \Closure $postAction = null)
    {
        $modelName = $this->getModel($data);

        /** @var $object AbstractModel */
        $object = new $modelName();
        $this->hydrator->hydrate($data, $object);

        if ($postAction) {
            $postAction($object);
        }

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
        $objects = array();
        foreach (explode(',', $id) as $id) {
            $object = $this->getRepository()->findOneBy(array('id' => $id));
            if (!$object) {
                $this->getResponse()->setStatusCode(404);
                return;
            }
            $objects[] = $object;
        }

        // if we have multiple ids to output
        if (count($objects) > 1) {
            $result = new JsonModel($this->hydrator->extractArray($objects, $this->getJsonConfig()));
        } else {
            $result = new JsonModel($this->hydrator->extract($objects[0], $this->getJsonConfig()));
        }
        return $result;
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
        $this->hydrator->hydrate($data, $object);
        $this->getEntityManager()->flush();
        $this->getResponse()->setStatusCode(201);
        return new JsonModel($this->hydrator->extract($object, $this->getJsonConfig()));
    }

}
