<?php

namespace ApplicationTest\Service;

/**
 * @group Service
 */
class AuthorizationServiceTest extends \ApplicationTest\Controller\AbstractController
{

    public function testAuthorizationService()
    {
        $geoname = new \Application\Model\Geoname('tst geoname');

        $user = new \Application\Model\User('test user');
        $user->setPassword('foo');

        $survey = new \Application\Model\Survey('test survey');
        $survey->setCode('test code');

        $survey2 = new \Application\Model\Survey('test survey2');
        $survey2->setCode('test code2');

        $questionnaire = new \Application\Model\Questionnaire();
        $questionnaire->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime())->setSurvey($survey)->setGeoname($geoname);

        $questionnaire2 = new \Application\Model\Questionnaire();
        $questionnaire2->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime())->setSurvey($survey2)->setGeoname($geoname);

        $filterSet = new \Application\Model\FilterSet('filterSet 1');
        $filterSet2 = new \Application\Model\FilterSet('filterSet 2');
        $filterSet3 = new \Application\Model\FilterSet('filterSet 3');

        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');
        $filter3 = new \Application\Model\Filter('filter 3');
        $filter11 = new \Application\Model\Filter('filter 11'); // child of $f1
        $filter12 = new \Application\Model\Filter('filter 12'); // child of $f1
        $filter21 = new \Application\Model\Filter('filter 21'); // child of $f2
        $filter22 = new \Application\Model\Filter('filter 22'); // child of $f2
        $filter1->addChild($filter11)->addChild($filter12);
        $filter2->addChild($filter21)->addChild($filter22);

        $filterSet->addFilter($filter1)->addFilter($filter2);
        $filterSet2->addFilter($filter1)->addFilter($filter3);
        $filterSet3->addFilter($filter1)->addFilter($filter2);

        $role = new \Application\Model\Role('role global');
        $roleSurvey = new \Application\Model\Role('role survey');
        $roleQuestionnaire = new \Application\Model\Role('role questionnaire');
        $roleFilterSet = new \Application\Model\Role('role filterset');

        $permission = new \Application\Model\Permission('permission global');
        $permissionSurvey = new \Application\Model\Permission('permission survey');
        $permissionQuestionnaire = new \Application\Model\Permission('permission questionnaire');
        $permissionFilterSet = new \Application\Model\Permission('permission filterset');
        $permissionAnswer = $this->getEntityManager()->getRepository('Application\Model\Permission')->findOneByName('Answer-update');

        $role->addPermission($permission);
        $role->addPermission($permissionSurvey);
        $role->addPermission($permissionQuestionnaire);
        $role->addPermission($permissionFilterSet);

        $roleSurvey->addPermission($permission);
        $roleSurvey->addPermission($permissionSurvey);

        $roleQuestionnaire->addPermission($permission);
        $roleQuestionnaire->addPermission($permissionQuestionnaire);
        $roleQuestionnaire->addPermission($permissionAnswer);

        $roleFilterSet->addPermission($permission);
        $roleFilterSet->addPermission($permissionFilterSet);

        $userSurvey = new \Application\Model\UserSurvey();
        $userSurvey->setUser($user)->setSurvey($survey)->setRole($roleSurvey);

        $userQuestionnaire = new \Application\Model\UserQuestionnaire();
        $userQuestionnaire->setUser($user)->setQuestionnaire($questionnaire)->setRole($roleQuestionnaire);

        $userFilterSet = new \Application\Model\UserFilterSet();
        $userFilterSet->setUser($user)->setFilterSet($filterSet)->setRole($roleFilterSet);

        $userFilterSet3 = new \Application\Model\UserFilterSet();
        $userFilterSet3->setUser($user)->setFilterSet($filterSet3)->setRole($roleFilterSet);

        $this->getEntityManager()->persist($geoname);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($survey2);
        $this->getEntityManager()->persist($questionnaire);
        $this->getEntityManager()->persist($questionnaire2);
        $this->getEntityManager()->persist($filterSet);
        $this->getEntityManager()->persist($filterSet2);
        $this->getEntityManager()->persist($filterSet3);
        $this->getEntityManager()->persist($filter1);
        $this->getEntityManager()->persist($filter2);
        $this->getEntityManager()->persist($filter3);
        $this->getEntityManager()->persist($filter11);
        $this->getEntityManager()->persist($filter12);
        $this->getEntityManager()->persist($filter21);
        $this->getEntityManager()->persist($filter22);
        $this->getEntityManager()->persist($role);
        $this->getEntityManager()->persist($roleSurvey);
        $this->getEntityManager()->persist($roleQuestionnaire);
        $this->getEntityManager()->persist($roleFilterSet);
        $this->getEntityManager()->persist($permission);
        $this->getEntityManager()->persist($permissionSurvey);
        $this->getEntityManager()->persist($permissionQuestionnaire);
        $this->getEntityManager()->persist($permissionFilterSet);
        $this->getEntityManager()->persist($userSurvey);
        $this->getEntityManager()->persist($userQuestionnaire);
        $this->getEntityManager()->persist($userFilterSet);
        $this->getEntityManager()->persist($userFilterSet3);
        $this->getEntityManager()->flush();

        /* @var $auth \Application\Service\AuthorizationService */
        $auth = $this->getApplicationServiceLocator()->get('ZfcRbac\Service\AuthorizationService');

        /* @var \ZfcRbac\Service\RoleService $roleService */
        $roleService = $this->getApplicationServiceLocator()->get('ZfcRbac\Service\RoleService');

        $identityProvider = $this->getApplicationServiceLocator()->get('ApplicationTest\Service\FakeIdentityProvider');
        $identityProvider->setIdentity(null);

        $this->assertTrue($roleService->matchIdentityRoles(['anonymous']), 'Not logged in users have builtin anonymous role');
        $this->assertFalse($roleService->matchIdentityRoles(['member']), 'Not logged in users does not have builtin member role');

        $identityProvider->setIdentity($user);
        $this->assertFalse($roleService->matchIdentityRoles(['anonymous']), 'Logged in users does not have builtin anonymous role');
        $this->assertTrue($roleService->matchIdentityRoles(['member']), 'Logged in users have builtin member role');
        $this->assertFalse($auth->isGranted('non existing permission name'), 'non existing permission is denied');

        // Test without context
        $this->assertTrue($auth->isGranted($permission->getName()), 'without context, global permission is granted');
        $this->assertTrue($auth->isGranted($permissionSurvey->getName()), 'without context, survey permission is granted');
        $this->assertTrue($auth->isGranted($permissionQuestionnaire->getName()), 'without context, questionnaire permission is granted');
        $this->assertTrue($auth->isGranted($permissionFilterSet->getName()), 'without context, filterSet permission is granted');

        // Test with Survey context
        $this->assertTrue($auth->isGrantedWithContext($survey, $permission->getName()), 'with survey context, global permission is still granted');
        $this->assertTrue($auth->isGrantedWithContext($survey, $permissionSurvey->getName()), 'with survey context, survey permission is granted');
        $this->assertFalse($auth->isGrantedWithContext($survey, $permissionQuestionnaire->getName()), 'with survey context, questionnaire permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($survey, $permissionFilterSet->getName()), 'with survey context, filterSet permission is denied');

        // Test with Questionnaire context
        $this->assertTrue($auth->isGrantedWithContext($questionnaire, $permission->getName()), 'with questionnaire context, global permission is still granted');
        $this->assertFalse($auth->isGrantedWithContext($questionnaire, $permissionSurvey->getName()), 'with questionnaire context, survey permission is denied');
        $this->assertTrue($auth->isGrantedWithContext($questionnaire, $permissionQuestionnaire->getName()), 'with questionnaire context, questionnaire permission is granted');
        $this->assertFalse($auth->isGrantedWithContext($questionnaire, $permissionFilterSet->getName()), 'with questionnaire context, filterSet permission is granted');

        // Test with Filterset context
        $this->assertTrue($auth->isGrantedWithContext($filterSet, $permission->getName()), 'with filterSet context, global permission is still granted');
        $this->assertFalse($auth->isGrantedWithContext($filterSet, $permissionSurvey->getName()), 'with filterSet context, survey permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($filterSet, $permissionQuestionnaire->getName()), 'with filterSet context, questionnaire permission is denied');
        $this->assertTrue($auth->isGrantedWithContext($filterSet, $permissionFilterSet->getName()), 'with filterSet context, filterSet permission is granted');

        // Test with wrong Survey context
        $this->assertFalse($auth->isGrantedWithContext($survey2, $permission->getName()), 'with wrong survey context, global permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($survey2, $permissionSurvey->getName()), 'with wrong survey context, survey permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($survey2, $permissionQuestionnaire->getName()), 'with wrong survey context, questionnaire permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($survey2, $permissionFilterSet->getName()), 'with wrong survey context, filterSet permission is denied');

        // Test with wrong Questionnaire context
        $this->assertFalse($auth->isGrantedWithContext($questionnaire2, $permission->getName()), 'with wrong questionnaire context, global permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($questionnaire2, $permissionSurvey->getName()), 'with wrong questionnaire context, survey permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($questionnaire2, $permissionQuestionnaire->getName()), 'with wrong questionnaire context, questionnaire permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($questionnaire2, $permissionFilterSet->getName()), 'with wrong questionnaire context, filterSet permission is denied');

        // Test with wrong FilterSet context
        $this->assertFalse($auth->isGrantedWithContext($filterSet2, $permission->getName()), 'with wrong filterSet context, global permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($filterSet2, $permissionSurvey->getName()), 'with wrong filterSet context, survey permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($filterSet2, $permissionQuestionnaire->getName()), 'with wrong filterSet context, questionnaire permission is denied');
        $this->assertFalse($auth->isGrantedWithContext($filterSet2, $permissionFilterSet->getName()), 'with wrong filterSet context, filterSet permission is denied');

        // test permissions on filters
        $this->assertCount(3, $filter1->getRoleContext(''), 'filter 1 should have 3 filterSets');
        $this->assertFalse($auth->isGrantedWithContext($filter1->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 1 & 2 & 3, permission is denied, restricted by filterset 2');
        $this->assertCount(2, $filter2->getRoleContext(''), 'filter 2 should have 1 filterSets');
        $this->assertTrue($auth->isGrantedWithContext($filter2->getRoleContext('read'), $permissionFilterSet->getName()), 'with filterset 1 & 3 context, permission is granted');
        $this->assertCount(1, $filter3->getRoleContext(''), 'filter 3 should have 2 filterSets');
        $this->assertFalse($auth->isGrantedWithContext($filter3->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 2 , permission is denied');

        // test permissions on child filters
        $this->assertCount(3, $filter11->getRoleContext(''), 'filter 11 should have 2 filterSets');
        $this->assertFalse($auth->isGrantedWithContext($filter11->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 11 , permission is denied');
        $this->assertCount(3, $filter12->getRoleContext(''), 'filter 12 should have 2 filterSets');
        $this->assertFalse($auth->isGrantedWithContext($filter12->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 12 , permission is denied');
        $this->assertCount(2, $filter21->getRoleContext(''), 'filter 21 should have 2 filterSets');
        $this->assertTrue($auth->isGrantedWithContext($filter21->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 21 , permission is granted');
        $this->assertCount(2, $filter22->getRoleContext(''), 'filter 22 should have 2 filterSets');
        $this->assertTrue($auth->isGrantedWithContext($filter22->getRoleContext(''), $permissionFilterSet->getName()), 'with filterset 21 , permission is granted');

        // Test error messages
        $this->assertTrue($auth->isActionGranted($filterSet, 'read'));
        $this->assertNull($auth->getMessage(), 'no message with granted action');
        $this->assertFalse($auth->isActionGranted($filterSet, 'update'));
        $expectedMessage = 'Insufficient access rights for permission "FilterSet-update" on "FilterSet#' . $filterSet->getId() . ' (filterSet 1)" with your current roles [member, role filterset] in contexts [FilterSet#' . $filterSet->getId() . ' (filterSet 1)]';
        $this->assertSame($expectedMessage, $auth->getMessage(), 'error message for single context object');
        $this->assertTrue($auth->isActionGranted($filter1, 'read'));
        $this->assertNull($auth->getMessage(), 'no message with granted action');
        $this->assertFalse($auth->isActionGranted($filter1, 'update'));
        $expectedMessage = 'Insufficient access rights for permission "Filter-update" on "Filter#' . $filter1->getId() . ' (filter 1)" with your current roles [member, role filterset] in contexts [FilterSet#' . $filterSet->getId() . ' (filterSet 1), FilterSet#' . $filterSet2->getId() . ' (filterSet 2), FilterSet#' . $filterSet3->getId() . ' (filterSet 3)]';
        $this->assertSame($expectedMessage, $auth->getMessage(), 'error message for multiple context object');

        // Test assertions, that we can modify an answer, but only for non-validated questionnaire
        $answer = new \Application\Model\Answer();
        $answer->setQuestionnaire($questionnaire);
        $this->assertTrue($auth->isActionGranted($answer, 'update'), 'Answers can be modified if questionnaire status is "new"');
        $questionnaire->setStatus(\Application\Model\QuestionnaireStatus::$VALIDATED);
        $this->assertFalse($auth->isActionGranted($answer, 'update'), 'Answers cannot be modified if questionnaire status "validated"');
        $this->assertEquals('Answers cannot be modified when questionnaire is marked as validated', $auth->getMessage(), 'Answers cannot be modified if questionnaire status "validated"');

        // Test MultipleRoleContext
        $multiWithSingleContext = new \Application\Service\MultipleRoleContext([$survey]);
        $this->assertTrue($auth->isGrantedWithContext($multiWithSingleContext, $permissionSurvey->getName()), 'should be granted, because multiRoleContext actually has only a single context');
        $multiAllMustBeGranted = new \Application\Service\MultipleRoleContext([$survey, $survey2], true);
        $this->assertFalse($auth->isGrantedWithContext($multiAllMustBeGranted, $permissionSurvey->getName()), 'should be denied, because multiRoleContext include survey2 on which we don\'t have access');
        $multiAllMustBeGranted = new \Application\Service\MultipleRoleContext([$survey2, $survey], true);
        $this->assertFalse($auth->isGrantedWithContext($multiAllMustBeGranted, $permissionSurvey->getName()), 'should be denied, because multiRoleContext include survey2 on which we don\'t have access');
        $multiAtLeastOneMustBeGranted = new \Application\Service\MultipleRoleContext([$survey, $survey2], false);
        $this->assertTrue($auth->isGrantedWithContext($multiAtLeastOneMustBeGranted, $permissionSurvey->getName()), 'should be denied, because multiRoleContext include survey2 on which we don\'t have access');
        $multiAtLeastOneMustBeGranted = new \Application\Service\MultipleRoleContext([$survey2, $survey], false);
        $this->assertTrue($auth->isGrantedWithContext($multiAtLeastOneMustBeGranted, $permissionSurvey->getName()), 'should be denied, because multiRoleContext include survey2 on which we don\'t have access');

        // Test with non persistent context
        $nonPersistedSurvey = new \Application\Model\Survey();
        $userSurvey2 = new \Application\Model\UserSurvey();
        $userSurvey->setUser($user)->setSurvey($nonPersistedSurvey)->setRole($roleSurvey);
        $this->assertTrue($auth->isGrantedWithContext($nonPersistedSurvey, $permission->getName()), 'permission with non persistent context is granted');
    }

}
