<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 *
 * @ORM\Entity(repositoryClass="Application\Repository\RoleRepository")
 */
class Role extends AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $canValidateQuestionnaire;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $canLinkOfficialQuestion;

    /**
     * Set name
     *
     * @param string $name
     * @return Role
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
     * Set canValidateQuestionnaire
     *
     * @param boolean $canValidateQuestionnaire
     * @return Role
     */
    public function setCanValidateQuestionnaire($canValidateQuestionnaire)
    {
        $this->canValidateQuestionnaire = $canValidateQuestionnaire;

        return $this;
    }

    /**
     * Get canValidateQuestionnaire
     *
     * @return boolean 
     */
    public function getCanValidateQuestionnaire()
    {
        return $this->canValidateQuestionnaire;
    }

    /**
     * Set canLinkOfficialQuestion
     *
     * @param boolean $canLinkOfficialQuestion
     * @return Role
     */
    public function setCanLinkOfficialQuestion($canLinkOfficialQuestion)
    {
        $this->canLinkOfficialQuestion = $canLinkOfficialQuestion;

        return $this;
    }

    /**
     * Get canLinkOfficialQuestion
     *
     * @return boolean 
     */
    public function getCanLinkOfficialQuestion()
    {
        return $this->canLinkOfficialQuestion;
    }

    /**
     * Get modifier
     *
     * @return integer 
     */
    public function getModifier()
    {
        return $this->modifier;
    }

}
