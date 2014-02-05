<?php

namespace Application\Traits;

trait FlatHierarchic
{

    /**
     * Get a tree of elements from a single root element relative to a parent relation
     *
     * @param $objects a list of prepared elements in assoc array with reference to parent
     * @param $referenceObject key of the reference to parent
     * @param int $rootElementId default id of root parent element
     * @internal param $questions
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFlatHierarchyWithSingleRootElement($objects, $referenceObject, $rootElementId = 0)
    {
        $objectsByParent = array();
        foreach ($objects as $object) {
            if (empty($object[$referenceObject]['id'])) {
                $object[$referenceObject]['id'] = $rootElementId;
            }
            $objectsByParent[$object[$referenceObject]['id']][] = $object;
        }

        if ($objects) {
            $elements = $this->createTree($objectsByParent, $objectsByParent[$rootElementId], 0);
        } else {
            $elements = array();
        }
        return $elements;
    }

    /**
     * When retrieving tree with single element root, the top level elements refers to the parent.
     * That behavior may be unwanted and this function removes the reference to the single root element on top hierarchic elements
     *
     * @param $objects a list of prepared elements in assoc array with reference to parent
     * @param $referenceObject key of the reference to parent
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFlatHierarchyWithMultipleRootElements($objects, $referenceObject)
    {
        $elements = $this->getFlatHierarchyWithSingleRootElement($objects, $referenceObject);
        $elements = array_map(function($el) use ($referenceObject) {
            if ($el[$referenceObject]['id'] == 0) {
                unset($el[$referenceObject]);
                return $el;
            }
            return $el;

        }, $elements);

        return $elements;
    }

    /**
     * Generate tree from given root element
     *
     * @param $objectsByParent
     * @param $parent
     * @param $deep
     * @return array
     */
    private function createTree(&$objectsByParent, $parent, $deep)
    {
        $tree = array();
        foreach ($parent as $child) {

            $child['level'] = $deep;
            if (isset($objectsByParent[$child['id']])) {
                $children = $this->createTree($objectsByParent, $objectsByParent[$child['id']], $deep + 1);
                $tree[] = $child;
                $tree = array_merge($tree, $children);
            } else {
                $tree[] = $child;
            }
        }

        return $tree;
    }

}
