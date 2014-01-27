<?php

namespace Application\Traits;

trait FlatHierarchic
{

    /**
     * Get AbstractQuestions
     *
     * @param $questions
     * @param $jsonConfig
     * @param $hydrator
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFlatHierarchy($objects, $referenceObject)
    {
        $new = array();
        $firstId = null;
        foreach ($objects as $a) {
            if (empty($a[$referenceObject]['id'])) {
                $a[$referenceObject]['id'] = 0;
            }
            $new[$a[$referenceObject]['id']][] = $a;
        }

        if ($objects) {
            $questions = $this->createTree($new, $new[0], 0);
        } else {
            $questions = array();
        }
        return $questions;
    }




    public function createTree(&$list, $parent, $deep)
    {
        $tree = array();
        foreach ($parent as $l) {

            $l['level'] = $deep;
            if (isset($list[$l['id']])) {
                $children = $this->createTree($list, $list[$l['id']], $deep + 1);
                $tree[] = $l;
                $tree = array_merge($tree, $children);
            } else {
                $tree[] = $l;
            }
        }

        return $tree;
    }


}
