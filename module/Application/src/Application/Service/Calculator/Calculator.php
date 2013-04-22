<?php

namespace Application\Service\Calculator;

use Application\Model\Answer;
use Application\Model\Question;
use Application\Model\Category;
use Application\Model\Part;
use Application\Model\Questionnaire;
use Application\Model\CategoryFilterComponent;
use Application\Model\Filter;

/**
 * Common base class for various computation. It includes a local instance cache.
 * That means a single instance of Calculator cannot be used if the data model
 * changes. Once computation was started and data model changes, then you *MUST*
 * create a new instance of Calculator to start from scratch (empty caches).
 */
class Calculator
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    use \Application\Traits\EntityManagerAware;

    private $cacheComputeCategoryFilterComponent = array();
    private $cacheComputeQuestionnaire = array();

    /**
     * Returns the computed value for the specified categoryFilterComponent
     * @param \Application\Model\CategoryFilterComponent $filterComponent
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Part $part
     * @return type
     */
    public function computeCategoryFilterComponent(CategoryFilterComponent $filterComponent, Questionnaire $questionnaire, Part $part = null)
    {
        $key = spl_object_hash($filterComponent) . spl_object_hash($questionnaire) . ($part ? spl_object_hash($part) : null);

        if ( array_key_exists($key, $this->cacheComputeCategoryFilterComponent)) {
            return $this->cacheComputeCategoryFilterComponent[$key];
        }

        $result = null;
        foreach ($filterComponent->getCategories() as $category) {
            $computed = $this->computeQuestionnaire($questionnaire, $category, $part);
            if (!is_null($computed)) {
                $result += $computed;
            }
        }


        $this->cacheComputeCategoryFilterComponent[$key] = $result;

        return $result;
    }

    /**
     * Returns the computed value of the given category, based on the questionnaire's available answers
     * @param \Application\Model\Category $category
     * @param \Application\Model\Part $part
     * @return float|null null if no answer at all, otherwise the value
     */
    public function computeQuestionnaire(Questionnaire $questionnaire, Category $category, Part $part = null)
    {
        $key = spl_object_hash($questionnaire) . spl_object_hash($category) . ($part ? spl_object_hash($part) : null);
        if (array_key_exists($key, $this->cacheComputeQuestionnaire)) {
            return $this->cacheComputeQuestionnaire[$key];
        }

        $result = $this->computeQuestionnaireInternal($questionnaire, $category, new \Doctrine\Common\Collections\ArrayCollection(), $part);

        $this->cacheComputeQuestionnaire[$key] = $result;

        return $result;
    }

    /**
     * Returns the computed value of the given category, based on the questionnaire's available answers
     * @param \Application\Model\Category $category
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedCategories will be used to avoid duplicates
     * @param \Application\Model\Part $part
     * @return float|null null if no answer at all, otherwise the value
     */
    private function computeQuestionnaireInternal(Questionnaire $questionnaire, Category $category, \Doctrine\Common\Collections\ArrayCollection $alreadySummedCategories, Part $part = null)
    {
        // Avoid duplicates
        if ($alreadySummedCategories->contains($category)) {
            return null;
        } else {
            $alreadySummedCategories->add($category);
        }

        // If the category have a specified answer, returns it (skip all computation)
        foreach ($questionnaire->getAnswers() as $answer) {
            $answerCategory = $answer->getQuestion()->getCategory()->getOfficialCategory() ? : $answer->getQuestion()->getCategory();
            if ($answerCategory == $category && $answer->getPart() == $part) {

                $alreadySummedCategories->add(true);
                return $answer->getValueAbsolute();
            }
        }


        // Summer to sum values of given categories, but only if non-null (to preserve null value if no answer at all)
        $summer = function(\IteratorAggregate $categories) use ($questionnaire, $part, $alreadySummedCategories) {
                    $sum = null;
                    foreach ($categories as $c) {
                        $summandValue = $this->computeQuestionnaireInternal($questionnaire, $c, $alreadySummedCategories, $part);
                        if (!is_null($summandValue)) {
                            $sum += $summandValue;
                        }
                    }

                    return $sum;
                };

        // First, attempt to sum summands
        $sum = $summer($category->getSummands());

        // If no sum so far, we use children instead. This is "normal case"
        if (is_null($sum)) {
            $sum = $summer($category->getChildren());
        }

        return $sum;
    }

}
