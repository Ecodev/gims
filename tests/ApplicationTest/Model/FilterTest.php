<?php

namespace ApplicationTest\Model;

class FilterTest extends AbstractModel
{

    public function testChildrenRelation()
    {
        $parent = new \Application\Model\Filter();
        $child = new \Application\Model\Filter();
        $this->assertCount(0, $parent->getChildren(), 'collection is initialized on creation');
        $parent->addChild($child);
        $this->assertCount(1, $parent->getChildren(), 'parent must be notified when child is added');
        $this->assertSame($child, $parent->getChildren()->first(), 'original child can be retreived from parent');
        $this->assertCount(1, $child->getParents(), 'child should have parent');
        $this->assertSame($parent, $child->getParents()->first(), 'original parent should be retrieved from child');

        $otherParent = new \Application\Model\Filter();
        $otherParent->addChild($child);
        $this->assertCount(1, $parent->getChildren(), 'original parent should still have child');
        $this->assertCount(1, $otherParent->getChildren(), 'new parent should have also added child');
        $this->assertSame($child, $otherParent->getChildren()->first(), 'original child can be retreived from new parent');
        $this->assertCount(2, $child->getParents(), 'child should have parent');
        $this->assertSame($parent, $child->getParents()->first(), 'original parent should be retrieved from child');
        $this->assertSame($otherParent, $child->getParents()->last(), 'original parent should be retrieved from child');
    }

    public function testQuestionsRelatedToFilters()
    {
        $filter = new \Application\Model\Filter();
        $this->assertCount(0, $filter->getQuestions(), 'collection is initialized on creation');

        // create 2 surveys and 4 questions
        $survey1 = new \Application\Model\Survey();
        $survey2 = new \Application\Model\Survey();
        $q1 = new \Application\Model\Question\NumericQuestion();
        $q1->setSurvey($survey1);
        $q2 = new \Application\Model\Question\NumericQuestion();
        $q2->setSurvey($survey1);
        $q3 = new \Application\Model\Question\NumericQuestion();
        $q3->setSurvey($survey2);
        $q4 = new \Application\Model\Question\NumericQuestion();
        $q4->setSurvey($survey2);

        // assign same filter to each question
        $q1->setFilter($filter);
        $q2->setFilter($filter);
        $q3->setFilter($filter);
        $q4->setFilter($filter);
        $this->assertCount(4, $filter->getQuestions(), 'filter should have two questions');
        $this->assertSame($filter, $q1->getFilter(), 'filter shoud be the same');
        $this->assertSame($filter, $q2->getFilter(), 'filter shoud be the same');
        $this->assertSame($q1->getFilter(), $q2->getFilter(), 'questions should have the same filter');

        $allQuestions = new \Doctrine\Common\Collections\ArrayCollection();
        $allQuestions->add($q1);
        $allQuestions->add($q2);
        $allQuestions->add($q3);
        $allQuestions->add($q4);

        $questionsSurvey1 = new \Doctrine\Common\Collections\ArrayCollection();
        $questionsSurvey1->add($q1);
        $questionsSurvey1->add($q2);

        $questionsSurvey2 = new \Doctrine\Common\Collections\ArrayCollection();
        $questionsSurvey2->add($q3);
        $questionsSurvey2->add($q4);

        $this->assertEquals($allQuestions, $filter->getQuestions(), 'filter questions are not the original questions');
        $this->assertEquals($questionsSurvey1, $filter->getQuestions($survey1), 'questions do not correspond to the ones of the 1st survey');
        $this->assertEquals($questionsSurvey2, $filter->getQuestions($survey2), 'questions do not correspond to the ones of the 2nd survey');

        // change filter for question 1
        $filter2 = new \Application\Model\Filter();
        $q1->setFilter($filter2);
        $this->assertSame($filter2, $q1->getFilter(), 'should have new filter');
        $this->assertNotContains($q1, $filter->getQuestions(), 'should not contain question1');
        $this->assertNotContains($q1, $filter->getQuestions($survey1), 'should not contain question1');

        $allQuestions->removeElement($q1);
        $questionsSurvey1->removeElement($q1);

        $this->assertCount(3, $filter->getQuestions(), 'filter should have 4 less 1 question ');
        $this->assertCount(1, $filter->getQuestions($survey1), 'filter should have 2 less 1 question');
        $this->assertEquals($allQuestions, $filter->getQuestions(), 'questions do not correspond');
        $this->assertEquals($questionsSurvey1->first(), $filter->getQuestions($survey1)->first(), 'questions do not correspond to the ones of the 1st survey');
    }

    public function testFilterSetRelatedToFilters()
    {
        $filterSet = new \Application\Model\FilterSet();
        $this->assertCount(0, $filterSet->getFilters(), 'collection is initialized on creation');

        $f1 = new \Application\Model\Filter();
        $f2 = new \Application\Model\Filter();
        $f3 = new \Application\Model\Filter();

        $filters = new \Doctrine\Common\Collections\ArrayCollection();
        $filters->add($f1);
        $filters->add($f2);
        $filters->add($f3);

        $filterSet->setFilters($filters);
        $this->assertCount(3, $filterSet->getFilters(), 'should contain the filters');
        $this->assertEquals($filters, $filterSet->getFilters(), 'should contain the same filters');
        $this->assertContains($filterSet, $f1->getFilterSets(), 'should contain the filterset');
    }
}
