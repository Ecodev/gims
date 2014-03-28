<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Common properties to "apply" a rule to something
 * @ORM\MappedSuperclass
 */
abstract class AbstractUsage extends \Application\Model\AbstractModel
{

    /**
     * @var Rule
     *
     * @ORM\ManyToOne(targetEntity="Rule")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $rule;

    /**
     * @var Part
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Part")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $part;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $justification;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false, options={"default" = 0})
     */
    private $sorting = 0;

    /**
     * @inheritdoc
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'justification',
            'rule',
            'part',
        ));
    }

    /**
     * Set rule
     *
     * @param Rule $rule
     * @return FilterQuestionnaireUsage
     */
    public function setRule(Rule $rule)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * Get rule
     *
     * @return Rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Set part
     *
     * @param \Application\Model\Part $part
     * @return FilterQuestionnaireUsage
     */
    public function setPart(\Application\Model\Part $part)
    {
        $this->part = $part;

        return $this;
    }

    /**
     * Get part
     *
     * @return \Application\Model\Part
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * Set justification
     *
     * @param string $justification
     * @return FilterQuestionnaireUsage
     */
    public function setJustification($justification)
    {
        $this->justification = $justification;

        return $this;
    }

    /**
     * Get justification
     *
     * @return string
     */
    public function getJustification()
    {
        return $this->justification;
    }

    /**
     * Set sorting
     *
     * @param integer $sorting
     * @return AbstractQuestion
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

}
