<?php

namespace ApplicationTest\Service;

use Application\Service\MultipleRoleContext;
use Application\Model\Survey;

/**
 * @group Service
 */
class MultipleRoleContextTest extends \ApplicationTest\Controller\AbstractController
{

    public function testMerge()
    {
        $survey = new Survey();
        $context = new MultipleRoleContext();
        $this->assertEquals(0, $context->count(), 'collection should be empty');

        $context->add(null);
        $this->assertEquals(0, $context->count(), 'null value should be ignored silently');

        $context->add($survey);
        $this->assertEquals(1, $context->count(), 'valid context can be added');

        $context->add($survey);
        $this->assertEquals(1, $context->count(), 'duplicated values are ignored');

        $context->merge(null);
        $this->assertEquals(1, $context->count(), 'null value should be ignored silently when merging');

        $context->merge(new Survey());
        $this->assertEquals(2, $context->count(), 'single context can be merged');

        $context->merge([new Survey(), new Survey()]);
        $this->assertEquals(4, $context->count(), 'array of single context can be merged');

        $otherContext = new MultipleRoleContext([new Survey(), new Survey()]);
        $this->assertEquals(2, $otherContext->count(), 'can create context with array of single context');

        $context->merge($otherContext);
        $this->assertEquals(6, $context->count(), 'multiple context can be merged');
    }

}
