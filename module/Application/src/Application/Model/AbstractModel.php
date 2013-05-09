<?php

namespace Application\Model;

use Application\Module;
use Doctrine\ORM\Mapping as ORM;
use Zend\Code\Reflection\MethodReflection;

/**
 * Base class for all objects stored in database.
 *
 * It includes an automatic mechanism to timestamp objects with date and user.
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractModel implements PropertiesUpdatableInterface
{

    private static $now;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $dateCreated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $dateModified;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(onDelete="SET NULL")
     * })
     */
    private $creator;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $modifier;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return AbstractModel
     */
    private function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set dateModified
     *
     * @param \DateTime $dateModified
     *
     * @return AbstractModel
     */
    private function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Set creator
     *
     * @param User $creator
     *
     * @return AbstractModel
     */
    private function setCreator(User $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set modifier
     *
     * @param User $modifier
     *
     * @return AbstractModel
     */
    private function setModifier(User $modifier = null)
    {
        $this->modifier = $modifier;

        return $this;
    }

    /**
     * Get modifier
     *
     * @return User
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Returns now, always same value for a single PHP execution
     *
     * @return \DateTime
     */
    private static function getNow()
    {
        if (!self::$now) {
            self::$now = new \DateTime();
        }

        return self::$now;
    }

    /**
     * Returns currently logged user
     *
     * @return User
     */
    private static function getCurrentUser()
    {
        $sm = Module::getServiceManager();
        $auth = $sm->get('zfcuser_auth_service');

        return $auth->getIdentity();
    }

    /**
     * Automatically called by Doctrine when the object is saved for the first time
     *
     * @ORM\PrePersist
     */
    public function timestampCreation()
    {
        $this->setDateCreated(self::getNow());
        $this->setCreator(self::getCurrentUser());
    }

    /**
     * Automatically called by Doctrine when the object is updated
     *
     * @ORM\PreUpdate
     */
    public function timestampModification()
    {
        $this->setDateModified(self::getNow());
        $this->setModifier(self::getCurrentUser());
    }

    /**
     * Sanitize data. We don't want to update certain properties.
     *
     * @todo move me somewhere else long term. Service?
     *
     * @param array $data
     *
     * @return array
     */
    private function sanitizeData($data)
    {
        foreach (array('id', 'dateCreated', 'dateModified') as $value) {
            unset($data[$value]);
        }
        return $data;
    }

    /**
     * Update property of $this
     *
     * @param array $data
     *
     * @return $this
     */
    public function updateProperties($data)
    {
        $sanitizedData = $this->sanitizeData($data);

        foreach ($sanitizedData as $key => $value) {
            if (is_array($value)) {
                $getter = 'get' . ucfirst($key);

                if (method_exists($this, $getter)) {
                    /** @var $object AbstractModel */
                    $object = call_user_func(array($this, $getter));

                    if (is_null($object) && !empty($value['id'])) {

                        // Check what kind of parameter type is taken by the setter as input.
                        $modelName = $this->getModelName($getter);
                        $object = $this->getObject($modelName, $value['id']);
                    }
                    $value = $object->updateProperties($value);
                } else {
                    $logger = Module::getServiceManager()->get('Zend\Log');
                    $logger->info('[WARNING] implement me! Can not persist data. Missing method ' . $getter);

                    // Get or create object from the storage
                    #$modelName = 'Application\Model\Answer' <-- do something better than that
                    #$object = $this->getObject($modelName, $id);
                    #$object->updateProperties($data);
                }
            }

            // Assemble setter method
            $setter = 'set' . ucfirst($key);

            // Bonus: code below enabled a short hand "syntax" when assembling a request on the client side.
            // e.g $data[question] = id instead of $data[question] = array('id' => id)

            // Check what kind of parameter type is taken by the setter as input.
            $modelName = $this->getModelName($setter);

            // If model name is suitable and given $value is numerical, get one from the storage.
            if (is_numeric($value) && preg_match('/Application\\\Model/is', $modelName)) {
                $value = $this->getObject($modelName, $value);
            }

            if (method_exists($this, $setter)) {
                call_user_func_array(array($this, $setter), array($value));
            }
        }
        return $this;
    }

    /**
     * Get an object given a Model name and an id
     *
     * @todo move me somewhere else long term. Service?
     *
     * @param string $modelName
     * @param int    $id
     *
     * @throws \Exception
     * @return AbstractModel
     */
    private function getObject($modelName, $id)
    {

        $repository = Module::getEntityManager()->getRepository($modelName);
        $records = $repository->findById($id);

        // raise exception if object does not exist in the DB.
        if (empty($records[0])) {
            $message = sprintf('No object "%s" found for id: %s', $modelName, $id);
            throw new \Exception($message, 1365442789);
        }
        return $records[0];
    }

    /**
     * Get input parameter type for a setter.
     *
     * @todo move me somewhere else long term. Service?
     *
     * @param string $methodName
     *
     * @return string
     */
    private function getModelName($methodName)
    {

        // Get the class name by "consulting" the stack
        $className = get_called_class();

        // If the method is a getter transform it into a setter
        // which makes it more straight forward retrieving the type using reflection.
        if (preg_match('/^get/is', $methodName)) {
            $methodName = preg_replace('/^get/is', 'set', $methodName);
        }

        $parameterType = null;

        $methods = new MethodReflection($className, $methodName);
        foreach ($methods->getParameters() as $parameter) {
            $parameterType = $parameter->getType();
            break; // should be only one parameter in context of setter
        }

        return $parameterType;
    }
}