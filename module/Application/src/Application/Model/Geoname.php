<?php

namespace Application\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Geoname. Data are imported from http://www.geonames.org, but only partially for
 * what we actually need.
 * @ORM\Entity(repositoryClass="Application\Repository\GeonameRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Geoname extends AbstractModel
{

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var \CrEOF\Spatial\DBAL\Types\GeometryType
     * @ORM\Column(type="geometry", nullable=true)
     */
    private $geometry;

    /**
     * Additional formulas to apply to compute regression lines
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\Application\Model\Rule\FilterGeonameUsage", mappedBy="geoname")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     */
    private $filterGeonameUsages;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $iso3;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Questionnaire", mappedBy="geoname")
     */
    private $questionnaires;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Geoname")
     * @ORM\OrderBy({"name" = "ASC"})
     * @ORM\JoinTable(name="geoname_children",
     *      inverseJoinColumns={@ORM\JoinColumn(name="child_geoname_id", onDelete="CASCADE")}
     *      )
     */
    private $children;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->filterGeonameUsages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->questionnaires = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), [
            'name',
        ]);
    }

    /**
     * Set name
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get formulas
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFilterGeonameUsages()
    {
        return $this->filterGeonameUsages;
    }

    /**
     * Notify the Geoname that it was added to FilterGeonameUsage relation.
     * This should only be called by FilterGeonameUsage::setGeoname()
     * @param Rule\FilterGeonameUsage $usage
     * @return self
     */
    public function filterGeonameUsageAdded(Rule\FilterGeonameUsage $usage)
    {
        $this->getFilterGeonameUsages()->add($usage);

        return $this;
    }

    /**
     * Set iso3
     *
     * @param string $iso3
     * @return self
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;

        return $this;
    }

    /**
     * Get iso3. If not null it means geoname is a country
     *
     * @return string|null
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getQuestionnaires()
    {
        return $this->questionnaires;
    }

    /**
     * Notify the Geoname that it was added to Questionnaire.
     * This should only be called by Questionnaire::setGeoname()
     * @param Questionnaire $questionnaire
     * @return self
     */
    public function questionnaireAdded(Questionnaire $questionnaire)
    {
        $this->getQuestionnaires()->add($questionnaire);

        return $this;
    }

    /**
     * Get children
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get children recursively
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllChildren()
    {
        $children = $this->getChildren()->toArray();
        foreach ($children as $child) {
            $children = array_merge($children, $child->getAllChildren()->toArray());
        }

        return new ArrayCollection($children);
    }

    /**
     * Automatically called by Doctrine when the object is modified whatsoever to invalid computing cache
     * @ORM\PostPersist
     * @ORM\PreUpdate
     * @ORM\PreRemove
     */
    public function invalidateCache()
    {
        $key = 'geoname:' . $this->getId();
        $cache = \Application\Module::getServiceManager()->get('Calculator\Cache');
        $cache->removeItem($key);
    }

}
