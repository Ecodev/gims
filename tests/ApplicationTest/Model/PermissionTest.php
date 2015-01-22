<?php

namespace ApplicationTest\Model;

/**
 * @group Model
 */
class PermissionTest extends AbstractModel
{

    public function permissionNamesDataProvider()
    {
        return [
            [new \Application\Model\Survey(), 'read', 'Survey-read'],
            [new \Application\Model\Question\NumericQuestion(), 'create', 'Question-create'],
            [new \Application\Model\Question\Chapter(), 'create', 'Question-create'],
            [new \Application\Model\Rule\Rule(), 'update', 'Rule-update'],
            [new \Application\Repository\SurveyRepository(null, new \Doctrine\ORM\Mapping\ClassMetadata('a')), 'read', 'Survey-read'],
            [new \Application\Repository\QuestionRepository(null, new \Doctrine\ORM\Mapping\ClassMetadata('a')), 'read', 'Question-read'],
        ];
    }

    /**
     * @dataProvider permissionNamesDataProvider
     */
    public function testPermissionNames($object, $action, $expected)
    {
        $this->assertSame($expected, \Application\Model\Permission::getPermissionName($object, $action));
    }

}
