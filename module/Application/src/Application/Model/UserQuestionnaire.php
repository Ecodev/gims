<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserQuestionnaire
 *
 * @ORM\Entity(repositoryClass="Application\Repository\UserQuestionnaireRepository")
 */
class UserQuestionnaire extends AbstractModel
{

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="Questionnaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    private $questionnaire;

    /**
     * @var \Role
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    private $role;

    /**
     * Set "user"
     *
     * @param \User $"user"
     * @return UserQuestionnaire
     */
    public function setUser(\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get "user"
     *
     * @return \User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set questionnaire
     *
     * @param \Questionnaire $questionnaire
     * @return UserQuestionnaire
     */
    public function setQuestionnaire(\Questionnaire $questionnaire = null)
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    /**
     * Get questionnaire
     *
     * @return \Questionnaire 
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set role
     *
     * @param \Role $role
     * @return UserQuestionnaire
     */
    public function setRole(\Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Role 
     */
    public function getRole()
    {
        return $this->role;
    }

}
