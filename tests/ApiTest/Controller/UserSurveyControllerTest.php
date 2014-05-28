<?php

namespace ApiTest\Controller;

/**
 * @group Rest
 */
class UserSurveyControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'user', 'role', 'survey');
    }

    protected function getTestedObject()
    {
        return $this->userSurvey;
    }

    protected function getPossibleParents()
    {
        return [
            $this->user,
            $this->survey,
        ];
    }

}
