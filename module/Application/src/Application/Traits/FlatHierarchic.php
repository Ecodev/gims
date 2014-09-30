<?php

namespace Application\Traits;

use \Application\Utility;

trait FlatHierarchic
{

    /**
     * Get a tree of elements from a single root element relative to a parent relation
     * @param array $objects a list of prepared elements in assoc array with reference to parent
     * @param string $referenceObject key of the reference to parent
     * @param integer $rootElementId default id of root parent element
     * @return array
     */
    public function getFlatHierarchyWithSingleRootElement(array $objects, $referenceObject, $rootElementId = 0)
    {
        $objectsByParent = array();
        foreach ($objects as $object) {
            // if there is no parent or if object has parent, but the parent is not in collection : set object as root
            if (!isset($object[$referenceObject]['id']) || empty($object[$referenceObject]['id']) || $object[$referenceObject]['id'] && !Utility::getObjectById($object[$referenceObject]['id'], $objects)) {
                $parentId = $rootElementId;
            } else {
                $parentId = $object[$referenceObject]['id'];
            }

            unset($object[$referenceObject]);
            $objectsByParent[$parentId][] = $object;
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
     * @param string $referenceObject key of the reference to parent
     * @return array
     */
    public function getFlatHierarchyWithMultipleRootElements(array $objects, $referenceObject)
    {
        $rootElements = array();
        $objectsByParent = array();
        $knownIds = array();

        foreach ($objects as $object) {
            $knownIds[] = $object['id'];
        }

        foreach ($objects as $object) {
            $parentId = @$object[$referenceObject]['id'];
            unset($object[$referenceObject]);

            // if object has no parent reference or if parent is not in given objects list, add object to rootElement
            if (!$parentId || !in_array($parentId, $knownIds)) {
                $rootElements[$object['id']] = $object;
            } else {
                $objectsByParent[$parentId][] = $object;
            }
        }

        if ($objects) {
            $elements = array();
            foreach ($rootElements as $rootId => $rootElement) {
                $rootElement['level'] = 0;
                array_push($elements, $rootElement);
                if (isset($objectsByParent[$rootId])) {
                    $elements = array_merge($elements, $this->createTree($objectsByParent, $objectsByParent[$rootId], 1));
                }
            }
        } else {
            $elements = array();
        }

        return $elements;
    }

    /**
     * Generate tree from given root element
     * @param array $objectsByParent
     * @param array$parent
     * @param integer $deep
     * @return array
     */
    private function createTree(array &$objectsByParent, array $parent, $deep)
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
