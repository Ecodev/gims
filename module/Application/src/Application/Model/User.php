<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="`user`", uniqueConstraints={@ORM\UniqueConstraint(name="user_email",columns={"email"})})
 * @ORM\Entity(repositoryClass="Application\Repository\UserRepository")
 */
class User extends AbstractModel implements \ZfcUser\Entity\UserInterface, \ZfcRbac\Identity\IdentityInterface
{
    /**
     * @var array
     */
    protected static $jsonConfig
        = array(
            'name',
            'email',
            'state',
        );

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $password;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $state;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UserSurvey", mappedBy="user")
     */
    private $userSurveys;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UserQuestionnaire", mappedBy="user")
     */
    private $userQuestionnaires;
    private $roleContext;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userSurveys = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userQuestionnaires = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set state
     *
     * @param integer $state
     * @return User
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
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
     * @return User
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
     * Notify the user that it was added to UserSurvey relation.
     * This should only be called by UserQuestionnaire::setUser()
     * @param UserQuestionnaire $userQuestionnaire
     * @return User
     */
    public function userQuestionnaireAdded(UserQuestionnaire $userQuestionnaire)
    {
        $this->getUserQuestionnaires()->add($userQuestionnaire);

        return $this;
    }

    /**
     * Set roles context to then query getRoles(). This MUST be followed by resetRolesContext() as soon as possible.
     * @param \Application\Service\RoleContextInterface $context
     */
    public function setRolesContext(\Application\Service\RoleContextInterface $context)
    {
        if (!$context->getId()) {
            throw new \InvalidArgumentException('Context must return a valid ID. To get a valid ID from Doctrine, use: $this->getEntityManager()->persist($context);');
        }

        $this->roleContext = array(get_class($context) => $context->getId());
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
     */
    public function getRoles()
    {
        // If we are here, it means there is at least a logged in user,
        // which means he has, at the very least, the hardcoded role of member
        $roles = array('member');

        // If there is no context, or the context matches, add roles from survey
        foreach ($this->getUserSurveys() as $userSurvey) {
            if (!$this->roleContext || @$this->roleContext['Application\Model\Survey'] == $userSurvey->getSurvey()->getId()) {
                $roles [] = $userSurvey->getRole()->getName();
            }
        }

        // If there is no context, or the context matches, add roles from questionnaire
        foreach ($this->getUserQuestionnaires() as $userQuestionnaire) {
            if (!$this->roleContext || @$this->roleContext['Application\Model\Questionnaire'] == $userQuestionnaire->getQuestionnaire()->getId()) {
                $roles [] = $userQuestionnaire->getRole()->getName();
            }
        }

        return $roles;
    }

    /**
     * Return a user gravatar
     */
    public function getGravatar()
    {
        return 'http://www.gravatar.com/avatar/' . md5( strtolower( trim( $this->getEmail() ) ) );
    }

}
