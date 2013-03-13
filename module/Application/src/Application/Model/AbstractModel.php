<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractModel
{

    private static $now;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
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
     *   @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    private $creator;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id")
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
     * @return AbstractModel
     */
    public function setDateCreated($dateCreated)
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
     * @return AbstractModel
     */
    public function setDateModified($dateModified)
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
     * @return AbstractModel
     */
    public function setCreator(User $creator = null)
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
     * @return AbstractModel
     */
    public function setModifier(User $modifier = null)
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
     * @return \DateTime
     */
    private static function getNow()
    {
        if (!self::$now)
            self::$now = new \DateTime();

        return self::$now;
    }

    /**
     * Returns currently logged user
     * @return User
     */
    private static function getCurrentUser()
    {
        $sm = \Application\Module::getServiceManager();
        $auth = $sm->get('zfcuser_auth_service');

        return $auth->getIdentity();
    }

    /**
     * Automatically called by Doctrine when the object is saved for the first time
     * @ORM\PrePersist
     */
    public function timestampCreation()
    {
        $this->setDateCreated(self::getNow());
        $this->setCreator(self::getCurrentUser());
    }

    /**
     * Automatically called by Doctrine when the object is updated
     * @ORM\PreUpdate
     */
    public function timestampModification()
    {
        $this->setDateModified(self::getNow());
        $this->setModifier(self::getCurrentUser());
    }

}