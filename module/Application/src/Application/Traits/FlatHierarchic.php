<?php

namespace Application\Traits;

trait FlatHierarchic
{

    /**
     * Get a tree of elements from a single root element relative to a parent relation
     * @param array $objects a list of prepared elements in assoc array with reference to parent
     * @param $referenceObject key of the reference to parent
     * @param int $rootElementId default id of root parent element
     * @internal param $questions
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFlatHierarchyWithSingleRootElement(array $objects, $referenceObject, $rootElementId = 0)
    {
        $objectsByParent = array();
        foreach ($objects as $object) {
            if (!isset($object[$referenceObject]['id']) || empty($object[$referenceObject]['id'])) {
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
     * @param array $objects a list of prepared elements in assoc array with reference to parent
     * @param $referenceObject key of the reference to parent
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFlatHierarchyWithMultipleRootElements2(array $objects, $referenceObject)
    {
        $elements = $this->getFlatHierarchyWithSingleRootElement($objects, $referenceObject);
        $elements = array_map(function ($el) use ($referenceObject) {
            if ($el[$referenceObject]['id'] == 0) {
                unset($el[$referenceObject]);

                return $el;
            }

            return $el;

        }, $elements);

        return $elements;
    }

    public function getFlatHierarchyWithMultipleRootElements($objects, $referenceObject)
    {
        $rootElements = array();
        $objectsByParent = array();
        $objectsById = array();

        foreach ($objects as $object) {
            $objectsById[$object['id']][] = $object;
        }

        foreach ($objects as $object) {
            // if object has no parent reference or if parent is not in given objects list, add object to rootElement
            if (!isset($object[$referenceObject]['id'])
                || isset($object[$referenceObject]['id']) && !isset($objectsById[$object[$referenceObject]['id']])
            ) {
                $rootElements[$object['id']] = $object;
            } else {
                $objectsByParent[$object[$referenceObject]['id']][] = $object;
            }
        }

        if ($objects) {
            $elements = array();
            foreach ($rootElements as $parentId => $rootElement) {
                $rootElement['level'] = 0;
                array_push($elements, $rootElement);
                if (isset($objectsByParent[$parentId])) {
                    $elements = array_merge($elements, $this->createTree($objectsByParent, $objectsByParent[$parentId], 1));
                }
            }
        } else {
            $elements = array();
        }

        return $elements;
    }

    /**
     * Generate tree from given root element
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
            $tree[] = $child;

            // if child is in objectsByParent that means he has children
            if (isset($objectsByParent[$child['id']])) {
                $children = $this->createTree($objectsByParent, $objectsByParent[$child['id']], $deep + 1);
                $tree = array_merge($tree, $children);
            }
        }

        return $tree;
    }

}
