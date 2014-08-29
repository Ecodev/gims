<?php

namespace ApiTest\Controller;

use Zend\Http\Request;
use Application\Model\Geoname;
use Application\Model\UserQuestionnaire;

/**
 * @group Rest
 */
class QuestionnaireControllerTest extends AbstractChildRestfulControllerTest
{

    use \ApiTest\Controller\Traits\ReferenceableInRule;

    protected function getAllowedFields()
    {
        return array('id', 'dateObservationStart', 'dateObservationEnd', 'survey', 'name', 'geoname', 'completed', 'spatial', 'dateLastAnswerModification', 'reporterNames', 'validatorNames', 'comments', 'status', 'permission');
    }

    protected function getTestedObject()
    {
        return $this->questionnaire;
    }

    protected function getPossibleParents()
    {
        return [
            $this->survey,
        ];
    }

    /**
     * Get suitable route for GET method.
     *
     * @param string $method
     *
     * @return string
     */
    protected function getRoute($method)
    {
        if ($method == 'getListViaSurvey') {
            return sprintf('/api/survey/%s/questionnaire', $this->survey->getId());
        } else {
            return parent::getRoute($method);
        }
    }

    public function testCanUpdateQuestionnaireGeoname()
    {
        // create new geoname
        $geoname = new Geoname('foo geoname');
        $this->getEntityManager()->persist($geoname);
        $this->getEntityManager()->flush();
        $expected = $this->questionnaire->getGeoname()->getId();

        $data = array(
            'geoname' => $geoname->getId(),
        );

        $this->dispatch($this->getRoute('put') . '?fields=geoname', Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertNotEquals($expected, $actual['geoname']['id']);
    }

    public function testCanCreateQuestionnaire()
    {
        // Questionnaire
        $data = array(
            'dateObservationStart' => '2013-05-22T00:00:00.000Z',
            'dateObservationEnd' => '2014-05-22T00:00:00.000Z',
            'geoname' => $this->geoname->getId(),
            'survey' => $this->survey->getId(),
            'status' => 'new',
        );

        $this->dispatch($this->getRoute('post') . '?fields=survey', Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['survey'], $actual['survey']['id']);
    }

    public function testSearchProvider()
    {
        return array(
            array('?q=test geoname', 1), // can search by geoname
            array('?q=code test survey', 1), // can search by survey code
            array('?q=code test survey test geoname', 1), // can search by both
        );
    }

    /**
     * @dataProvider testSearchProvider
     * @param string $params
     * @param integer $count
     */
    public function testSearch($params, $count)
    {
        $this->dispatch($this->getRoute('getListViaSurvey') . $params, Request::METHOD_GET);
        $actual = $this->getJsonResponse();

        $this->assertEquals($count, $actual['metadata']['totalCount'], 'result count does not match expectation');
    }

    public function testAnonymousCanGetPublishedQuestionnaire()
    {
        // Anonymous should not be able to get a questionnaire on which he has no access
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(403);
        $this->dispatch($this->getRoute('getListViaSurvey'), Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertEquals(0, $actual['metadata']['totalCount'], 'should be not able to be listed');

        // Publish the questionnaire
        $this->questionnaire = $this->getEntityManager()->merge($this->questionnaire);
        $this->questionnaire->setStatus(\Application\Model\QuestionnaireStatus::$PUBLISHED);
        $this->getEntityManager()->flush();

        // Should be able to get it now
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
        $this->dispatch($this->getRoute('getListViaSurvey'), Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertEquals(1, $actual['metadata']['totalCount'], 'should be able to be listed');
    }

    public function testCanGetQuestionnaireFromBothContexts()
    {
        $this->createAnotherQuestionnaire();

        // Check that we have exactly one role via survey
        $this->assertEquals(1, $this->user->getUserSurveys()->count(1), 'should have only one role on surveys');
        $this->assertEquals('Survey editor', $this->user->getUserSurveys()[0]->getRole()->getName(), 'should be Survey editor');

        // Check that we have exactly one role via questionnaire
        $this->assertEquals(1, $this->user->getUserQuestionnaires()->count(1), 'should have only one role on questionnaires');
        $this->assertEquals('Questionnaire reporter', $this->user->getUserQuestionnaires()[0]->getRole()->getName(), 'should be Questionnaire reporter');

        // Check that we have exactly 2 questionnaires
        $this->assertEquals(2, $this->survey->getQuestionnaires()->count(), 'should have two questionnaires');

        $this->dispatch($this->getRoute('getListViaSurvey') . '?fields=permissions', Request::METHOD_GET);
        $actual = $this->getJsonResponse();

        $this->assertEquals(2, $actual['metadata']['totalCount'], 'should be able to get both questionnaires, one via questionnaire context, and the other one via survey context');
    }

    public function testCanValidateAndPublishQuestionnaire()
    {
        $roleRepository = $this->getEntityManager()->getRepository('Application\Model\Role');
        $validator = $roleRepository->findOneByName('Questionnaire validator');
        $publisher = $roleRepository->findOneByName('Questionnaire publisher');

        $data = ['status' => 'validated'];
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);

        // Define user as questionnaire validator (the guy who can validate if questionnaire is correct)
        $userValidator = $this->createAnotherUser();
        $userQuestionnaire = new UserQuestionnaire();
        $this->questionnaire = $this->getEntityManager()->merge($this->questionnaire);
        $userQuestionnaire->setUser($userValidator)->setQuestionnaire($this->questionnaire)->setRole($validator);
        $this->getEntityManager()->persist($userQuestionnaire);
        $this->getEntityManager()->flush();

        // Now that we have proper role, it should be allowed
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['status'], $actual['status'], 'status should have been modified');

        // Now test publishing
        $data = ['status' => 'published'];
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);

        // Define user as questionnaire publisher
        $userPublisher = $this->createAnotherUser();
        $userQuestionnaire2 = new UserQuestionnaire();
        $this->questionnaire = $this->getEntityManager()->merge($this->questionnaire);
        $userQuestionnaire2->setUser($userPublisher)->setQuestionnaire($this->questionnaire)->setRole($publisher);
        $this->getEntityManager()->persist($userQuestionnaire2);
        $this->getEntityManager()->remove($userQuestionnaire);
        $this->getEntityManager()->flush();

        // Now that we have proper role, it should be allowed
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['status'], $actual['status'], 'status should have been modified');
    }

}
