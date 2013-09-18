<?php

namespace Application\Traits;

trait FlatHierarchicQuestions
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
    public function getFlatHierarchy($questions, $jsonConfig, $hydrator)
    {
        // prepare flat array of questions for then be reordered by Parent > childrens > childrens
        $flatQuestions = array();
        foreach ($questions as $question) {
            $flatQuestion = $hydrator->extract($question, $jsonConfig);
            array_push($flatQuestions, $flatQuestion);
        }

        $new = array();
        $firstId = null;
        foreach ($flatQuestions as $a) {
            if (empty($a['chapter']['id'])) {
                $a['chapter']['id'] = 0;
            }
            $new[$a['chapter']['id']][] = $a;
        }

        if ($flatQuestions) {
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
