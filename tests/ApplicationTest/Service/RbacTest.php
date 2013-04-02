<?php

namespace ApplicationTest\Service;

class RbacTest extends \ApplicationTest\Controller\AbstractController
{

    public function testRbac()
    {
        $user = new \Application\Model\User();
        $user->setPassword('foo')->setName('test user');

        $survey = new \Application\Model\Survey();
        $survey->setName('test survey')->setActive(true)->setCode('test code');

        $survey2 = new \Application\Model\Survey();
        $survey2->setName('test survey2')->setActive(true)->setCode('test code2');

        $questionnaire = new \Application\Model\Questionnaire();
        $questionnaire->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime())->setSurvey($survey);

        $questionnaire2 = new \Application\Model\Questionnaire();
        $questionnaire2->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime())->setSurvey($survey2);

        $role = new \Application\Model\Role('role global');
        $roleSurvey = new \Application\Model\Role('role survey');
        $roleQuestionnaire = new \Application\Model\Role('role questionnaire');

        $permission = new \Application\Model\Permission('permission global');
        $permissionSurvey = new \Application\Model\Permission('permission survey');
        $permissionQuestionnaire = new \Application\Model\Permission('permission questionnaire');

        $role->addPermission($permission);
        $role->addPermission($permissionSurvey);
        $role->addPermission($permissionQuestionnaire);

        $roleSurvey->addPermission($permission);
        $roleSurvey->addPermission($permissionSurvey);

        $roleQuestionnaire->addPermission($permission);
        $roleQuestionnaire->addPermission($permissionQuestionnaire);

        $userSurvey = new \Application\Model\UserSurvey();
        $userSurvey->setUser($user)->setSurvey($survey)->setRole($roleSurvey);

        $userQuestionnaire = new \Application\Model\UserQuestionnaire();
        $userQuestionnaire->setUser($user)->setQuestionnaire($questionnaire)->setRole($roleQuestionnaire);


        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($survey2);
        $this->getEntityManager()->persist($questionnaire);
        $this->getEntityManager()->persist($questionnaire2);
        $this->getEntityManager()->persist($role);
        $this->getEntityManager()->persist($roleSurvey);
        $this->getEntityManager()->persist($roleQuestionnaire);
        $this->getEntityManager()->persist($permission);
        $this->getEntityManager()->persist($permissionSurvey);
        $this->getEntityManager()->persist($permissionQuestionnaire);
        $this->getEntityManager()->persist($userSurvey);
        $this->getEntityManager()->persist($userQuestionnaire);
        $this->getEntityManager()->flush();


        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getApplicationServiceLocator()->get('ZfcRbac\Service\Rbac');

        $this->assertTrue($rbac->hasRole('anonymous'), 'Not logged in users have builtin anonymous role');
        $this->assertFalse($rbac->hasRole('member'), 'Not logged in users does not have builtin member role');

        $rbac->setIdentity($user);
        $this->assertFalse($rbac->hasRole('anonymous'), 'Logged in users does not have builtin anonymous role');
        $this->assertTrue($rbac->hasRole('member'), 'Logged in users have builtin member role');


        $this->assertFalse($rbac->isGranted('non existing permission name'), 'non existing permission is denied');

        // Test without context
        $this->assertTrue($rbac->isGranted($permission->getName()), 'without context, global permission is granted');
        $this->assertTrue($rbac->isGranted($permissionSurvey->getName()), 'without context, survey permission is granted');
        $this->assertTrue($rbac->isGranted($permissionQuestionnaire->getName()), 'without context, questionnaire permission is granted');

        // Test with Survey context
        $this->assertTrue($rbac->isGrantedWithContext($survey, $permission->getName()), 'with survey context, global permission is still granted');
        $this->assertTrue($rbac->isGrantedWithContext($survey, $permissionSurvey->getName()), 'with survey context, survey permission is granted');
        $this->assertFalse($rbac->isGrantedWithContext($survey, $permissionQuestionnaire->getName()), 'with survey context, questionnaire permission is denied');

        // Test with Questionnaire context
        $this->assertTrue($rbac->isGrantedWithContext($questionnaire, $permission->getName()), 'with questionnaire context, global permission is still granted');
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire, $permissionSurvey->getName()), 'with questionnaire context, survey permission is denied');
        $this->assertTrue($rbac->isGrantedWithContext($questionnaire, $permissionQuestionnaire->getName()), 'with questionnaire context, questionnaire permission is granted');

        // Test with wrong Survey context
        $this->assertFalse($rbac->isGrantedWithContext($survey2, $permission->getName()), 'with wrong survey context, global permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($survey2, $permissionSurvey->getName()), 'with wrong survey context, survey permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($survey2, $permissionQuestionnaire->getName()), 'with wrong survey context, questionnaire permission is denied');

        // Test with wrong Questionnaire context
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire2, $permission->getName()), 'with wrong questionnaire context, global permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire2, $permissionSurvey->getName()), 'with wrong questionnaire context, survey permission is denied');
        $this->assertFalse($rbac->isGrantedWithContext($questionnaire2, $permissionQuestionnaire->getName()), 'with wrong questionnaire context, questionnaire permission is denied');
    }

    public function testCannotGrantAccessWithNonPersistedContext()
    {
        $user = new \Application\Model\User();
        $user->setPassword('foo')->setName('test user');

        $nonPersistedSurvey = new \Application\Model\Survey();

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getApplicationServiceLocator()->get('ZfcRbac\Service\Rbac');
        $rbac->setIdentity($user);

        $this->setExpectedException('InvalidArgumentException');
        $rbac->isGrantedWithContext($nonPersistedSurvey, 'foo permission');
    }

}
