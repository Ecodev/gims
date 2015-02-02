<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserQuestionnaire links a user and a questionnaire to give him a role
 * for that questionnaire (hence permissions)
 * @ORM\Entity(repositoryClass="Application\Repository\UserQuestionnaireRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="user_questionnaire_unique",columns={"user_id", "questionnaire_id", "role_id"})})
 */
class UserQuestionnaire extends AbstractUserRole
{

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userQuestionnaires")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    protected $user;

    /**
     * @var Questionnaire
     * @ORM\ManyToOne(targetEntity="Questionnaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $questionnaire;

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), [
            'user',
            'role',
            'questionnaire',
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
        $user->userQuestionnaireAdded($this);

        return $this;
    }

    /**
     * Set questionnaire
     * @param Questionnaire $questionnaire
     * @return self
     */
    public function setQuestionnaire(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    /**
     * Get questionnaire
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleContext($action)
    {
        return $this->getQuestionnaire()->getSurvey();
    }
}
