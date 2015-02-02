<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class AnswerControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return ['id', 'valuePercent', 'valueAbsolute', 'valueText', 'isCheckboxChecked', 'valueChoice', 'part', 'question', 'valueUser'];
    }

    protected function getTestedObject()
    {
        return $this->answer;
    }

    protected function getPossibleParents()
    {
        return [
            $this->questionnaire,
        ];
    }

    public function testPostANewAnswerWithNestedObjectWillCreateIt()
    {
        // Question
        $data = [
            'valuePercent' => 0.6,
            'question' => [
                'id' => $this->question->getId(),
            ],
            'questionnaire' => [
                'id' => $this->questionnaire->getId(),
            ],
            'part' => [
                'id' => $this->part3->getId(),
            ],
        ];

        $this->dispatch($this->getRoute('post') . '?fields=questionnaire', Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['valuePercent'], $actual['valuePercent']);
        $this->assertEquals($data['questionnaire']['id'], $actual['questionnaire']['id'], 'should return specified fields, in addition to standard one');
    }

    public function testPostANewAnswerWithFlatObjectWillCreateIt()
    {
        // Question
        $data = [
            'valuePercent' => 0.6,
            'question' => $this->question->getId(),
            'questionnaire' => $this->questionnaire->getId(),
            'part' => $this->part3->getId(),
        ];

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['valuePercent'], $actual['valuePercent']);
    }

    public function testPostANewAnswerReturnsStatusCode403ForUserWithRoleAnonymous()
    {
        // Question
        $data = [
            'valuePercent' => 0.6,
            'question' => [
                'id' => $this->question->getId(),
            ],
            'questionnaire' => [
                'id' => $this->questionnaire->getId(),
            ],
            'part' => [
                'id' => $this->part->getId(),
            ],
        ];

        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCanUpdateValuePercentOfAnswer()
    {
        $expected = $this->answer->getValuePercent() + 0.2;
        $data = [
            'valuePercent' => $expected,
        ];

        $this->dispatch($this->getRoute('put') . '?fields=questionnaire', Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($expected, $actual['valuePercent'], 'it should be the new value set');
        $this->assertEquals($this->answer->getQuestionnaire()->getId(), $actual['questionnaire']['id'], 'should return specified fields, in addition to standard one');

        // Same with anonymous will fail
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }
}
