<?php

namespace Api\Controller;

use Api\Service\MetaModel;
use Application\Model\AbstractModel;
use Application\Traits\EntityManagerAware;
use Zend\View\Model\JsonModel;

abstract class AbstractRestfulController extends \Zend\Mvc\Controller\AbstractRestfulController
{

    use EntityManagerAware;

    /**
     * @var \Application\Service\AuthorizationService
     */
    private $auth;

    /**
     * @var MetaModel
     */
    private $metaModelService;

    /**
     * @var \Application\Service\Hydrator
     */
    protected $hydrator;

    /**
     * Returns Authorization service
     * @return \Application\Service\AuthorizationService
     */
    protected function getAuth()
    {
        if (!$this->auth) {
            $this->auth = $this->getServiceLocator()->get('ZfcRbac\Service\AuthorizationService');
        }

        return $this->auth;
    }

    /**
     * Throw an exception if action is denied
     * @param \Application\Model\AbstractModel $object
     * @param string $action
     * @throws \Application\Service\PermissionDeniedException
     */
    protected function checkActionGranted(AbstractModel $object, $action)
    {
        if (!$this->getAuth()->isActionGranted($object, $action)) {
            throw new \Application\Service\PermissionDeniedException($this->getAuth()->getMessage());
        }
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
     * Returns whether client asked to do only validation
     * @return boolean
     */
    protected function isOnlyValidation()
    {
        return !is_null($this->params()->fromQuery('validate'));
    }

    /**
     * Return an array of specified surveyTypes
     * @return array
     */
    protected function getSurveyTypes()
    {
        $surveyTypeParam = $this->params()->fromQuery('surveyType');
        $surveyTypes = [];
        if ($surveyTypeParam) {
            foreach (explode(',', $surveyTypeParam) as $type) {
                if ($type) {
                    $surveyTypes [] = \Application\Model\SurveyType::get($type);
                }
            }
        }

        return $surveyTypes;
    }

    /**
     * Must return an array of properties that will be exposed publicly via JSON.
     * If a property is actually an object itself, it must have a sub-array of properties
     *
     * @return array JSON configuration
     */
    protected function getJsonConfig()
    {
        $result = [];
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
     * Optionally return closures to override json properties
     * @return array
     */
    protected function getClosures()
    {
        return [];
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

        if (preg_match('/Usage$/', $shortClass)) {
            $shortClass = 'Rule\\' . $shortClass;
        }

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
     * Optionnal hook to do something after the object was created and flushed in database
     * @param \Application\Model\AbstractModel $object
     */
    protected function postCreate(AbstractModel $object, array $data)
    {
        // nothing to do
    }

    /**
     * @param array $data
     *
     * @param callable $postAction
     * @return JsonModel
     */
    public function create($data)
    {
        // Check that all required properties are given by the GUI
        $mandatoryProperties = $this->getMetaModelService()->getMandatoryProperties();
        $givenProperties = array_keys($data);
        $missingProperties = array_diff($mandatoryProperties, $givenProperties);
        if ($missingProperties) {
            throw new \Exception('Missing mandatory properties: ' . implode(', ', $missingProperties), 1368459231);
        }

        $modelName = $this->getModel();

        /** @var $object AbstractModel */
        $object = new $modelName();
        $this->hydrator->hydrate($data, $object);

        // If only want to validate, do it then return empty data
        if ($this->isOnlyValidation()) {
            $object->validate();
            $this->getResponse()->setStatusCode(200);

            return new JsonModel($this->hydrator->extract($object, $this->getJsonConfig()));
        }

        // If not allowed to create object, cancel everything
        $this->checkActionGranted($object, 'create');

        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();
        $this->getResponse()->setStatusCode(201);

        $result = $this->postCreate($object, $data);

        if (!$result) {
            $result = new JsonModel($this->hydrator->extract($object, $this->getJsonConfig()));
        }

        return $result;
    }

    /**
     * @param int $id
     *
     * @return JsonModel
     */
    public function delete($id)
    {
        $object = $this->getRepository()->findOneById($id);
        if (!$object) {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel(['message' => 'No object found']);
        }

        // If not allowed to delete object, cancel everything
        $this->checkActionGranted($object, 'delete');

        $this->getEntityManager()->remove($object);
        $this->getEntityManager()->flush();
        $this->getResponse()->setStatusCode(204);

        return new JsonModel(['message' => 'Deleted successfully']);
    }

    /**
     * Returns one object or a list of objects
     * If more than one object is wanted, return valid objects excluding those who have errors
     * If a single object is wanted and has an error (access not granted or not found) an error is returned
     * @todo : maybe return errored objects and valid objects in order to give information to client side
     * @param int $id
     * @return JsonModel
     */
    public function get($id)
    {
        $objects = [];
        $notFound = [];
        $notGranted = [];
        $ids = \Application\Utility::explodeIds($id);

        foreach ($ids as $id) {
            $object = $this->getRepository()->findOneById($id);
            if (!$object) {
                $notFound[] = $id;
                continue;
            }

            // If not allowed to read the object, cancel everything
            if ($this->getAuth()->isActionGranted($object, 'read')) {
                $objects[] = $object;
            } else {
                $notGranted[] = $id;
                if (count($ids) == 1) {
                    $objects[] = $object;
                }
            }
        }

        if (count($notFound) == count($ids)) {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel(['message' => 'No object found']);
        } elseif (count($notGranted) == count($ids)) {
            $this->checkActionGranted($this->getRepository()->findOneById($ids[0]), 'read');
        } else {
            $this->getResponse()->setStatusCode(200);
        }

        // if we have multiple IDs to output
        if (count($ids) > 1) {
            $result = new JsonModel($this->hydrator->extractArray($objects, $this->getJsonConfig()));
        } else {
            $result = new JsonModel($this->hydrator->extract($objects[0], $this->getJsonConfig()));
        }

        return $result;
    }

    /**
     * @return JsonModel
     */
    public function getList()
    {
        $objects = $this->getRepository()->getAllWithPermission($this->params()->fromQuery('permission', 'read'), $this->params()->fromQuery('q'));
        $jsonData = $this->paginate($objects);

        return new JsonModel($jsonData);
    }

    /**
     * Paginate an array of objects according to GET parameters
     * @param array $objects
     * @param boolean $dehydrate whether we should dehydrate objects
     * @return array pagination metata, and a subset of objects (optionnaly extracted by Hydrator)
     */
    protected function paginate(array $objects, $dehydrate = true)
    {
        $defaultPage = 1;
        $defautPerPage = 25;
        $page = (int) $this->params()->fromQuery('page', $defaultPage);
        $perPage = (int) $this->params()->fromQuery('perPage', $defautPerPage);

        if ($page < 1) {
            $page = $defaultPage;
        }

        $perPage = max(0, min(1000, $perPage));

        $paginatedObjects = array_slice($objects, ($page - 1) * $perPage, $perPage);

        $jsonData = [
            'metadata' => [
                'page' => $page,
                'perPage' => $perPage,
                'totalCount' => count($objects),
            ],
            'items' => $dehydrate ? $this->hydrator->extractArray($paginatedObjects, $this->getJsonConfig()) : $paginatedObjects,
        ];

        return $jsonData;
    }

    /**
     * Optionnal hook to do something after the object was updated and flushed in database
     * @param \Application\Model\AbstractModel $object
     */
    protected function postUpdate(AbstractModel $object, array $data)
    {
        // nothing to do
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return JsonModel
     */
    public function update($id, $data)
    {
        /** @var $object \Application\Model\AbstractModel */
        $object = $this->getRepository()->findOneById($id);

        if (!$object) {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel(['message' => 'No object found']);
        }

        // First, hydrate the object in case the hydration changes the context used for ACL evaluation
        $this->hydrator->hydrate($data, $object);

        // If only want to validate, do it then return modified object
        if ($this->isOnlyValidation()) {

            // If not allowed to read the object, cancel the validation
            $this->checkActionGranted($object, 'read');

            $object->validate();
            $this->getResponse()->setStatusCode(200);

            return new JsonModel($this->hydrator->extract($object, $this->getJsonConfig()));
        }

        // If not allowed to update the object, cancel everything
        $this->checkActionGranted($object, 'update');

        $this->getEntityManager()->flush();
        $this->getResponse()->setStatusCode(201);

        $result = $this->postUpdate($object, $data);

        if (!$result) {
            _log()->debug('nothing received from post update');
            $result = new JsonModel($this->hydrator->extract($object, $this->getJsonConfig()));
        } else {
            _log()->debug('youpi !!');
        }

        return $result;
    }

}
