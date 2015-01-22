<?php

namespace Application\Model\Rule;

use Application\Model\Filter;
use Application\Model\Geoname;
use Doctrine\ORM\Mapping as ORM;

/**
 * FilterGeonameUsage allows us to "apply" a formula to a filter-part couple, to be used for regression computation.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\FilterGeonameUsageRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="filter_geoname_usage_unique",columns={"filter_id", "geoname_id", "part_id", "rule_id"})})
 * @ORM\HasLifecycleCallbacks
 */
class FilterGeonameUsage extends AbstractUsage
{

    /**
     * @var Rule
     *
     * @ORM\ManyToOne(targetEntity="Rule", inversedBy="filterGeonameUsages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    protected $rule;

    /**
     * @var Filter
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Filter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $filter;

    /**
     * @var Geoname
     * @ORM\ManyToOne(targetEntity="Application\Model\Geoname", inversedBy="filterGeonameUsages")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $geoname;

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), [
            'filter',
            'geoname',
        ]);
    }

    /**
     * Set filter
     *
     * @param Filter $filter
     * @return self
     */
    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;

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
     * Set geoname
     *
     * @param Geoname $geoname
     *
     * @return Questionnaire
     */
    public function setGeoname(Geoname $geoname)
    {
        $this->geoname = $geoname;
        $geoname->filterGeonameUsageAdded($this);

        return $this;
    }

    /**
     * Get geoname
     *
     * @return Geoname
     */
    public function getGeoname()
    {
        return $this->geoname;
    }

    /**
     * Returns role contexts, which are all related questionnaires
     * @param string $action
     * @return \Application\Service\MultipleRoleContext
     */
    public function getRoleContext($action)
    {
        return new \Application\Service\MultipleRoleContext($this->getGeoname()->getQuestionnaires());
    }

    /**
     * Automatically called by Doctrine when the object is modified whatsoever to invalid computing cache
     * @ORM\PostPersist
     * @ORM\PreUpdate
     * @ORM\PreRemove
     */
    public function invalidateCache()
    {
        $cache = \Application\Module::getServiceManager()->get('Calculator\Cache');
        $key = 'F#' . $this->getFilter()->getId() . ',G#' . $this->getGeoname()->getId() . ',P#' . $this->getPart()->getId();
        $cache->removeItem($key);

        $key = $this->getCacheKey();
        $cache->removeItem($key);
    }

    public function getCacheKey()
    {
        return 'fgu:' . $this->getId();
    }

    public function getActivityData()
    {
        $data = parent::getActivityData();

        $data['filter'] = [
            'id' => $this->getFilter()->getId(),
            'name' => $this->getFilter()->getName(),
        ];

        $data['geoname'] = [
            'id' => $this->getGeoname()->getId(),
            'name' => $this->getGeoname()->getName(),
        ];

        return $data;
    }

}
