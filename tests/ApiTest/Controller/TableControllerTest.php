<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group ApiComputing
 */
class TableControllerTest extends \ApplicationTest\Controller\AbstractController
{

    use Traits\SupressDataSetOutput;

    public function setUp()
    {
        parent::setUp();
        $this->getEntityManager()->flush();
    }

    public function getValidDataFilterProvider()
    {
        return new \ApiTest\JsonFileIterator('tests/data/api/table/filter');
    }

    /**
     * @dataProvider getValidDataFilterProvider
     * @group LongTest
     */
    public function testGetValidDataFilter($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/table/filter?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->assertNumericJson($expectedJson, $this->getResponse()->getContent(), $message, $logFile);
    }

    public function getValidDataQuestionnaireProvider()
    {
        return new \ApiTest\JsonFileIterator('tests/data/api/table/questionnaire');
    }

    /**
     * @dataProvider getValidDataQuestionnaireProvider
     * @group LongTest
     */
    public function testGetValidDataQuestionnaire($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/table/questionnaire?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->assertNumericJson($expectedJson, $this->getResponse()->getContent(), $message, $logFile);
    }

    public function getValidDataCountryProvider()
    {
        return new \ApiTest\JsonFileIterator('tests/data/api/table/country');
    }

    /**
     * @dataProvider getValidDataCountryProvider
     * @group LongTest
     */
    public function testGetValidDataCountry($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/table/country?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->assertNumericJson($expectedJson, $this->getResponse()->getContent(), $message, $logFile);
    }
}
