<?php

namespace Application\Model;

use Application\Module;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base class for all objects stored in database.
 *
 * It includes an automatic mechanism to timestamp objects with date and user.
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractModel
{

    private static $now;

    /**
     * @var array
     */
    protected static $jsonConfig
        = array(
            'id'
        );

    /**
     * @var array
     */
    protected static $metadata = array(
        'dateCreated',
        'dateModified',
        'creator',
        'modifier',
    );

    /**
     * @return array
     */
    public static function getMetadata()
    {
        return self::$metadata;
    }

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
     */
    private $creator;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
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
    private function setDateCreated(\DateTime $dateCreated = null)
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
    private function setDateModified(\DateTime $dateModified = null)
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
     * @return array
     */
    public static function getJsonConfig()
    {
        $class = '\\' . get_called_class();
        return array_merge(self::$jsonConfig, $class::$jsonConfig);
    }

    /**
     * Tells whether the key is defined as possible.
     *
     * @param string $key
     *
     * @return array
     */
    public static function isPropertyInJsonConfig($key)
    {
        $class = '\\' . get_called_class();
        return in_array($key, $class::$jsonConfig);
    }
}