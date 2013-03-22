<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserQuestionnaire
 *
 * @ORM\Entity(repositoryClass="Application\Repository\UserQuestionnaireRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="user_questionnaire_unique",columns={"user_id", "questionnaire_id", "role_id"})})
 */
class UserQuestionnaire extends AbstractModel
{

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userQuestionnaires")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $user;

    /**
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="Questionnaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $questionnaire;

    /**
     * @var Role
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $role;

    /**
     * Set "user"
     *
     * @param User $user
     * @return UserQuestionnaire
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $user->userQuestionnaireAdded($this);

        return $this;
    }

    /**
     * Get "user"
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set questionnaire
     *
     * @param Questionnaire $questionnaire
     * @return UserQuestionnaire
     */
    public function setQuestionnaire(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    /**
     * Get questionnaire
     *
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set role
     *
     * @param Role $role
     * @return UserQuestionnaire
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

}
