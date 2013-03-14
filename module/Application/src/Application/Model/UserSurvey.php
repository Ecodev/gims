<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserSurvey
 *
 * @ORM\Entity(repositoryClass="Application\Repository\UserSurveyRepository")
 */
class UserSurvey extends AbstractModel
{

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $user;

    /**
     * @var Role
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $role;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $survey;

    /**
     * Set "user"
     *
     * @param User $user
     * @return UserSurvey
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set role
     *
     * @param Role $role
     * @return UserSurvey
     */
    public function setRole(Role $role = null)
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

    /**
     * Set survey
     *
     * @param Survey $survey
     * @return UserSurvey
     */
    public function setSurvey(Survey $survey = null)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * Get survey
     *
     * @return Survey 
     */
    public function getSurvey()
    {
        return $this->survey;
    }

}
