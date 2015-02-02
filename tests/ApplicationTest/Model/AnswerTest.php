<?php

namespace ApplicationTest\Model;

use Application\Model\Answer;

/**
 * @group Model
 */
class AnswerTest extends AbstractModel
{

    public function testValuePercentNullable()
    {
        $answer = new Answer();
        $this->assertSame(null, $answer->getValuePercent(), 'should be NULL by default');

        $answer->setValuePercent('1.74');
        $this->assertNotSame('1.74', $answer->getValuePercent(), 'should not never return string');
        $this->assertSame(1.74, $answer->getValuePercent(), 'should always return float');

        $answer->setValuePercent(null);
        $this->assertSame(null, $answer->getValuePercent(), 'should be able to re-set NULL');
    }
}
