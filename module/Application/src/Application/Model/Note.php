<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 * @ORM\Table(name="note")
 * @ORM\Entity(repositoryClass="Application\Repository\NoteRepository")
 */
class Note extends AbstractModel
{

    /**
     * @var string
     * @ORM\Column(type="string", length=1023, nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $attachmentName;

    /**
     * @var \Application\Model\Question\AbstractQuestion
     * @ORM\ManyToOne(targetEntity="\Application\Model\Question\AbstractQuestion")
     */
    private $question;

    /**
     * @var Questionnaire
     * @ORM\ManyToOne(targetEntity="Questionnaire")
     */
    private $questionnaire;

    /**
     * @var Survey
     * @ORM\ManyToOne(targetEntity="Survey")
     */
    private $survey;

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'description',
            'attachmentName'
        ));
    }

    /**
     * Set name
     *
     * @param string $description
     *
     * @return User
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set attachmentName
     *
     *     * @return User
     */
    public function setAttachmentName($attachmentName)
    {
        $this->attachmentName = $attachmentName;

        return $this;
    }

    /**
     * Get attachmentName
     *
     * @return string
     */
    public function getAttachmentName()
    {
        return $this->attachmentName;
    }

    /**
     * @param \Application\Model\Question\AbstractQuestion $question
     *
     * @return \Application\Model\Question\AbstractQuestion
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return \Application\Model\Question\AbstractQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param \Application\Model\Questionnaire $questionnaire
     *
     * @return \Application\Model\Questionnaire
     */
    public function setQuestionnaire($questionnaire)
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    /**
     * @return \Application\Model\Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * @param \Application\Model\Survey $survey
     *
     * @return \Application\Model\Survey
     */
    public function setSurvey($survey)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * @return \Application\Model\Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }

}
