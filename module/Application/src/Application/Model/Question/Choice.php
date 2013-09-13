<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;

/**
 * Choice is used to defines all possible choices for a Question of type QuestionType::$CHOICE
 *
 * @ORM\Entity(repositoryClass="Application\Repository\ChoiceRepository")
 */
class Choice extends \Application\Model\AbstractModel
{ /**
 * @var integer
 *
 * @ORM\Column(type="smallint", nullable=false, options={"default" = 0})
 */

    private $sorting = 0;

    /**
     * @var NumericQuestion
     *
     * @ORM\ManyToOne(targetEntity="ChoiceQuestion", inversedBy="choices")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $question;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=4, scale=3, nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $label;

    /**
     * @inheritdoc
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'value',
            'label',
            'sorting',
        ));
    }

    /**
     * @return ChoiceQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param ChoiceQuestion $question
     * @return $this
     */
    public function setQuestion(ChoiceQuestion $question)
    {
        $this->question = $question;
        $this->question->choiceAdded($this);

        return $this;
    }

    /**
     * Set sorting
     *
     * @param integer $sorting
     * @return NumericQuestion
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Get sorting
     *
     * @return integer
     */
    public function getSorting()
    {
        return (int) $this->sorting;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRoleContext($action)
    {
        return $this->getQuestion()->getRoleContext($action);
    }

}
