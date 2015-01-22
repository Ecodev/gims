<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserSurvey links a user and a survey to give him a role
 * for that survey (hence permissions)
 * @ORM\Entity(repositoryClass="Application\Repository\UserSurveyRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="user_survey_unique",columns={"user_id", "survey_id", "role_id"})})
 */
class UserSurvey extends AbstractUserRole
{

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userSurveys")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    protected $user;

    /**
     * @var Survey
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $survey;

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), [
            'user',
            'role',
            'survey',
        ]);
    }

    /**
     * Set "user"
     * @param User $user
     * @return self
     */
    public function setUser(User $user)
    {
        parent::setUser($user);
        $user->userSurveyAdded($this);

        return $this;
    }

    /**
     * Set survey
     * @param Survey $survey
     * @return self
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * Get survey
     * @return Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleContext($action)
    {
        return $this->getSurvey();
    }

}
