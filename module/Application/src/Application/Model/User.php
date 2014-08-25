<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 * @ORM\Table(name="`user`", uniqueConstraints={@ORM\UniqueConstraint(name="user_email",columns={"email"})})
 * @ORM\Entity(repositoryClass="Application\Repository\UserRepository")
 */
class User extends AbstractModel implements \ZfcUser\Entity\UserInterface, \ZfcRbac\Identity\IdentityInterface
{

    /**
     * Returns currently logged user or null
     * @return self|null
     */
    public static function getCurrentUser()
    {
        $sm = \Application\Module::getServiceManager();
        $identityProvider = $sm->get('ZfcRbac\Service\AuthorizationService');

        return $identityProvider->getIdentity();
    }

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $phone;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $skype;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $job;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ministry;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $zip;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var \Application\Model\Country
     * @ORM\ManyToOne(targetEntity="\Application\Model\Country")
     */
    private $country;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $state;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $lastLogin;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UserSurvey", mappedBy="user")
     */
    private $userSurveys;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UserQuestionnaire", mappedBy="user")
     */
    private $userQuestionnaires;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UserFilterSet", mappedBy="user")
     */
    private $userFilterSets;

    /**
     * @var \Application\Service\RoleContextInterface
     */
    private $roleContext;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->userSurveys = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userQuestionnaires = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userFilterSets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userFilters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'name',
            'email',
            'state',
            'lastLogin'
        ));
    }

    /**
     * Set name
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     * @param string $password
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set state
     * @param integer $state
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Forward method call, because we only have a name
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getUsername();
    }

    /**
     * Forward method call, because we only have a name
     * @return string
     */
    public function getUsername()
    {
        return $this->getName();
    }

    /**
     * Forward method call, because we only have a name
     * @return string
     */
    public function setDisplayName($displayName)
    {
        return $this->setUsername($displayName);
    }

    /**
     * Not implemented. Do absolutely nothing.
     */
    public function setId($id)
    {
        // This method must exists because of ZfcUser, but it must not do anything
        // It must *NOT* set the id, or it would break Doctrine, and open security breach via REST API to manually define ID
    }

    /**
     * Forward method call, because we only have a name
     * @return string
     */
    public function setUsername($username)
    {
        return $this->setName($username);
    }

    /**
     * Get userSurveys
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUserSurveys()
    {
        return $this->userSurveys;
    }

    /**
     * Notify the user that it was added to UserSurvey relation.
     * This should only be called by UserSurvey::setUser()
     * @param UserSurvey $userSurvey
     * @return self
     */
    public function userSurveyAdded(UserSurvey $userSurvey)
    {
        $this->getUserSurveys()->add($userSurvey);

        return $this;
    }

    /**
     * Get userQuestionnaires
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUserQuestionnaires()
    {
        return $this->userQuestionnaires;
    }

    /**
     * Get userFilterSets
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUserFilterSets()
    {
        return $this->userFilterSets;
    }

    /**
     * Notify the user that it was added to UserSurvey relation.
     * This should only be called by UserQuestionnaire::setUser()
     * @param UserQuestionnaire $userQuestionnaire
     * @return self
     */
    public function userQuestionnaireAdded(UserQuestionnaire $userQuestionnaire)
    {
        $this->getUserQuestionnaires()->add($userQuestionnaire);

        return $this;
    }

    /**
     * Notify the user that it was added to UserFilterSet relation.
     * This should only be called by UserFilterSet::setUser()
     * @param UserFilterSet UserFilterSet
     * @return self
     */
    public function userFilterSetAdded(UserFilterSet $userFilterSet)
    {
        $this->getUserFilterSets()->add($userFilterSet);

        return $this;
    }

    /**
     * Set roles context to then query getRoles(). This MUST be followed by resetRolesContext() as soon as possible.
     * @param \Application\Service\RoleContextInterface $context
     */
    public function setRolesContext(\Application\Service\RoleContextInterface $context)
    {
        $this->roleContext = $context;
    }

    /**
     * Resets roles context
     */
    public function resetRolesContext()
    {
        $this->roleContext = null;
    }

    /**
     * Return the roles currently active for this user and current role context (if any)
     * @return array
     */
    public function getRoles()
    {
        // If we are here, it means there is at least a logged in user,
        // which means he has, at the very least, the hardcoded role of member
        $roles = array('member');

        $roleRepository = \Application\Module::getEntityManager()->getRepository('\Application\Model\Role');
        $roleNames = $roleRepository->getAllRoleNames($this, $this->roleContext);

        return array_unique(array_merge($roles, $roleNames));
    }

    /**
     * Return a user gravatar
     */
    public function getGravatar()
    {
        return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($this->getEmail())));
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getSkype()
    {
        return $this->skype;
    }

    /**
     * @param string $skype
     * @return self
     */
    public function setSkype($skype)
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * @return string
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param string $job
     * @return self
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return string
     */
    public function getMinistry()
    {
        return $this->ministry;
    }

    /**
     * @param string $ministry
     * @return self
     */
    public function setMinistry($ministry)
    {
        $this->ministry = $ministry;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     * @return self
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return self
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return \Application\Model\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param \Application\Model\Country $country
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime $lastLogin
     * @return self
     */
    public function setLastLogin(\DateTime $lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

}
