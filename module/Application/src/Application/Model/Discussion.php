<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Discussion can be attached to various objects so users can debate on things
 * @ORM\Entity(repositoryClass="Application\Repository\DiscussionRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="discussion_unique_on_survey",columns={"survey_id"}, options={"where": "(((survey_id IS NOT NULL) AND (questionnaire_id IS NULL)) AND (survey_id IS NULL))"}),
 *     @ORM\UniqueConstraint(name="discussion_unique_on_questionnaire",columns={"survey_id"}, options={"where": "(((survey_id IS NULL) AND (questionnaire_id IS NOT NULL)) AND (survey_id IS NULL))"}),
 *     @ORM\UniqueConstraint(name="discussion_unique_on_answer",columns={"survey_id"}, options={"where": "(((survey_id IS NULL) AND (questionnaire_id IS NOT NULL)) AND (survey_id IS NOT NULL))"}),
 * })
 */
class Discussion extends AbstractModel
{

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OrderBy({"dateCreated" = "ASC", "id" = "ASC"})
     * @ORM\OneToMany(targetEntity="Application\Model\Comment", mappedBy="discussion")
     */
    private $comments;

    /**
     * @var Survey
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $survey;

    /**
     * @var Questionnaire
     * @ORM\ManyToOne(targetEntity="Questionnaire")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $questionnaire;

    /**
     * @var \Application\Model\Filter
     * @ORM\ManyToOne(targetEntity="\Application\Model\Filter")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $filter;

    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
        ));
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Notify the survey that it was added to the filter.
     * This should only be called by Comment::setDiscussion()
     *
     * @param \Application\Model\Comment $comment
     *
     * @return self
     */
    public function commentAdded(Comment $comment)
    {
        $this->getComments()->add($comment);

        return $this;
    }

    /**
     * @param \Application\Model\Survey $survey
     *
     * @return self
     */
    public function setSurvey(Survey $survey)
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

    /**
     * @param \Application\Model\Questionnaire $questionnaire
     *
     * @return self
     */
    public function setQuestionnaire(Questionnaire $questionnaire)
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
     * @param \Application\Model\Filter $filter
     *
     * @return self
     */
    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return \Application\Model\Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Returns the name of the discussion based on what it is linked to
     * @return string
     */
    public function getName()
    {
        if ($this->getSurvey()) {
            return $this->getSurvey()->getName();
        } elseif ($this->getFilter()) {
            return $this->getQuestionnaire()->getName() . ' - ' . $this->getFilter()->getName();
        } else {
            return $this->getQuestionnaire()->getName();
        }
    }

    /**
     * Returns the last comment if any
     * @return \Application\Model\Comment|null
     */
    public function getLastComment()
    {
        return $this->getComments()->last();
    }

}
