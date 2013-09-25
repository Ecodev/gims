<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterFormula allows us to "apply" a formula to a filter-part couple, to be used for regression computation.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\FilterFormulaRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="filter_formula_unique",columns={"filter_id", "part_id", "formula_id"})})
 */
class FilterFormula extends AbstractRelation
{

    /**
     * @var Filter
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Filter", inversedBy="filterFormulas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $filter;

    /**
     * @var Formula
     *
     * @ORM\ManyToOne(targetEntity="Formula")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $formula;

    /**
     * Set filter
     *
     * @param Filter $filter
     * @return FilterFormula
     */
    public function setFilter(\Application\Model\Filter $filter)
    {
        $this->filter = $filter;
        $filter->formulaAdded($this);

        return $this;
    }

    /**
     * Get filter
     *
     * @return \Application\Model\Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set formula
     *
     * @param Formula $formula
     * @return FilterFormula
     */
    public function setFormula(Formula $formula)
    {
        $this->formula = $formula;

        return $this;
    }

    /**
     * Get formula
     *
     * @return Formula
     */
    public function getFormula()
    {
        return $this->formula;
    }

}
