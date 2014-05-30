<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Rule is a way to define a value as a custom formula.
 *
 * Data integrity when deleting referenced objects is guaranteed by database
 * layer via triggers. And thus if the syntax change, the triggers must be
 * updated accordingly.
 *
 * Syntax is based on Excel formula syntax. Except cell references (eg: A2, B3)
 * must be entirely replaced with following syntax. There is different syntax
 * for the two different context.
 *
 * Basic context (Calculator\Calculator):
 * <ul>
 *     <li>
 *         Reference a Filter's value:
 *         <pre>{F#12,Q#34,P#56}</pre>
 *         <pre>{F#12,Q#34,P#56,L#2}</pre>
 *     </li>
 *     <li>
 *         Reference the Question name. It will return NULL if no non-NULL Answer is found
 *         for the Filter and Questionnaire pair submitted.
 *         <pre>{F#12,Q#34}</pre>
 *     </li>
 *     <li>
 *         Reference a QuestionnaireUsage's value:
 *         <pre>{R#12,Q#34,P#56}</pre>
 *     </li>
 *     <li>
 *         Reference a population data of the questionnaire's country:
 *         <pre>{Q#34,P#56}</pre>
 *     </li>
 * </ul>
 *
 * Regression context (Calculator\Jmp):
 * <ul>
 *     <li>
 *         Reference a Filter's regression value for same part and year. Or 1 year earlier, or 3 years later:
 *         <pre>{F#12,P#current,Y0}</pre>
 *         <pre>{F#12,P#current,Y-1}</pre>
 *         <pre>{F#12,P#current,Y+3}</pre>
 *     </li>
 *     <li>
 *         Reference a list of available filter values for all current questionnaires.
 *         The result use Excel array constant syntax (eg: "{1,2,3,4,5}")
 *         <pre>{F#12,Q#all}</pre>
 *     </li>
 *     <li>
 *         Reference the cumulated population for all current questionnaires for the given part:
 *         <pre>{Q#all,P#56}</pre>
 *     </li>
 *     <li>
 *         Reference the current year, this may be useful to adapt formula for exceptional cases:
 *         <pre>{Y}</pre>
 *     </li>
 * </ul>
 *
 * Both context:
 * <ul>
 *     <li>
 *         Reference the value if computed without this formula. It allows
 *         for formulas chaining:
 *         <pre>{self}</pre>
 *     </li>
 * </ul>
 *
 * Where:
 * - F = Filter
 * - Q = Questionnaire
 * - P = Part
 * - R = Rule
 * - L = Level, only two possibilities: absent, or exactly "L#2" to indicate Level 2
 * - Y = Year
 *
 * In the first case, F, and in all cases Q and P, can have the value "current" instead of actual ID. It means
 * that the current Filter, Questionnaire or Part should be used, instead of one selected
 * by its ID. This syntax should be prefered when possible to maximise Rule re-use.
 *
 * An entire formula could be:
 * <pre>=IF(ISTEXT({F#12,Q#34}), SUM({F#12,Q#34,P#56}, {R#2,Q#34,P#56}), {R#2,Q#34,P#56})</pre>
 *
 * Or the more re-usable version:
 * <pre>=IF(ISTEXT({F#12,Q#current}), SUM({F#12,Q#current,P#current}, {R#2,Q#current,P#current}), {R#2,Q#current,P#current})</pre>
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\RuleRepository")
 */
class Rule extends \Application\Model\AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=4096)
     */
    private $formula;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="FilterQuestionnaireUsage", mappedBy="rule")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     */
    private $filterQuestionnaireUsages;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="QuestionnaireUsage", mappedBy="rule")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     */
    private $questionnaireUsages;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="FilterGeonameUsage", mappedBy="rule")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     */
    private $filterGeonameUsages;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->filterQuestionnaireUsages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->questionnaireUsages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->filterGeonameUsages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Return the name of this rule (for end-user)
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set formula
     *
     * @param string $formula
     * @return Formula
     */
    public function setFormula($formula)
    {
        $this->formula = $formula;

        return $this;
    }

    /**
     * Get formula
     *
     * @return string
     */
    public function getFormula()
    {
        return $this->formula;
    }

    /**
     * Get filterQuestionnaireUsages
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFilterQuestionnaireUsages()
    {
        return $this->filterQuestionnaireUsages;
    }

    /**
     * Get questionnaireUsages
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getQuestionnaireUsages()
    {
        return $this->questionnaireUsages;
    }

    /**
     * Get filterGeonameUsages
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFilterGeonameUsages()
    {
        return $this->filterGeonameUsages;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'name',
            'formula',
        ));
    }

    /**
     * Returns role contexts, which are all related questionnaires
     * @param string $action
     * @return \Application\Service\MultipleRoleContext
     */
    public function getRoleContext($action)
    {
        $contexts = new \Application\Service\MultipleRoleContext([], true);

        foreach ($this->getFilterQuestionnaireUsages() as $usage) {
            $contexts->add($usage->getQuestionnaire());
        }

        foreach ($this->getQuestionnaireUsages() as $usage) {
            $contexts->add($usage->getQuestionnaire());
        }

        foreach ($this->getFilterGeonameUsages() as $usage) {
            foreach ($usage->getGeoname()->getQuestionnaires() as $questionnaire) {

                $contexts->add($questionnaire);
            }
        }

        return $contexts->count() ? $contexts : null;
    }

    /**
     * Notify the rule that it was added to Usage relation.
     * This should only be called by AbstractUsage::setRule()
     * @param AbstractUsage $usage
     * @return self
     */
    public function usageAdded(AbstractUsage $usage)
    {
        if ($usage instanceof FilterQuestionnaireUsage) {
            $this->getFilterQuestionnaireUsages()->add($usage);
        } elseif ($usage instanceof QuestionnaireUsage) {
            $this->getQuestionnaireUsages()->add($usage);
        } elseif ($usage instanceof FilterGeonameUsage) {
            $this->getFilterGeonameUsages()->add($usage);
        } else {
            throw new \Exception('Unsupported usage added');
        }

        return $this;
    }

}
