<?php

namespace Application\Model\Rule;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Rule is a way to define a value as a custom formula.
 *
 * Data integrity when deleting referenced objects is guaranteed by database
 * layer via triggers. And thus if the syntax change, the triggers must be
 * updated accordingly.
 *
 * For a description of formula syntax, @see docs/content/rule.rst
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\RuleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Rule extends \Application\Model\AbstractRecordableActivity implements ReferencableInterface
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
     * @ORM\Column(type="string", length=4096, options={"default" = "="})
     */
    private $formula = '=';

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="FilterQuestionnaireUsage", mappedBy="rule", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     */
    private $filterQuestionnaireUsages;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="QuestionnaireUsage", mappedBy="rule", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     */
    private $questionnaireUsages;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="FilterGeonameUsage", mappedBy="rule", fetch="EXTRA_LAZY")
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
        return array_merge(parent::getJsonConfig(), [
            'name',
            'formula',
        ]);
    }

    /**
     * Returns role contexts, which are all related questionnaires
     * @param string $action
     * @return \Application\Service\MultipleRoleContext
     */
    public function getRoleContext($action)
    {
        $repositoryQ = \Application\Module::getEntityManager()->getRepository(\Application\Model\Questionnaire::class);
        $questionnaires = $repositoryQ->getAllFromRule($this);
//        w($a);
//        return null;

        $contexts = new \Application\Service\MultipleRoleContext($questionnaires);
//
//        foreach ($this->getFilterQuestionnaireUsages() as $usage) {
//            $contexts->add($usage->getQuestionnaire());
//        }
//
//        foreach ($this->getQuestionnaireUsages() as $usage) {
//            $contexts->add($usage->getQuestionnaire());
//        }
//
//        foreach ($this->getFilterGeonameUsages() as $usage) {
//            foreach ($usage->getGeoname()->getQuestionnaires() as $questionnaire) {
//                $contexts->add($questionnaire);
//            }
//        }
        // If we try to delete a rule, we must also consider the side-effect it may have on other Rules that use this rule
        if ($action == 'delete') {
            $repository = \Application\Module::getEntityManager()->getRepository(\Application\Model\Rule\Rule::class);
            $rulesWithReference = $repository->getAllReferencing($this);
            foreach ($rulesWithReference as $rule) {
                $contexts->merge($rule->getRoleContext($action));
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

    /**
     * Validate the object and throw an exception if invalid.
     * This is called automtically by Doctrine.
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @throws \Application\Validator\Exception
     */
    public function validate()
    {
        $validator = new \Application\Validator\Rule();
        if (!$validator->isValid($this)) {
            throw new \Application\Validator\Exception($validator->getMessages());
        }
    }

    public function getStructure()
    {
        $parser = new \Application\Service\Syntax\Parser();
        $parser->setServiceLocator(\Application\Module::getServiceManager());

        return $parser->getStructure($this->getFormula());
    }

    /**
     * Automatically called by Doctrine when the object is modified whatsoever to invalid computing cache
     * @ORM\PostPersist
     * @ORM\PreUpdate
     * @ORM\PreRemove
     */
    public function invalidateCache()
    {
        $key = 'rule:' . $this->getId();
        $cache = \Application\Module::getServiceManager()->get('Calculator\Cache');
        $cache->removeItem($key);
    }
    /**
     * Automatically called by Doctrine when the object is deleted to also delete dependencies
     * @ORM\PreRemove
     */
    public function deleteDependencies()
    {
        $ruleRepository = \Application\Module::getEntityManager()->getRepository(\Application\Model\Rule\Rule::class);
        $ruleRepository->deleteDependencies($this);
    }

    public function getActivityData()
    {
        return [
            'name' => $this->getName(),
        ];
    }

    public function getSymbol()
    {
        return 'R';
    }
}
