<?php

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;

abstract class AbstractRepository extends EntityRepository
{

    public function getAllWithPermission($parentName, \Application\Model\AbstractModel $parent = null)
    {
        if ($parentName) {
            return $this->findBy(array($parentName => $parent));
        } else {
            return $this->findAll();
        }
    }

    protected function getPermissionDql($context, $permission)
    {
        if ($context == 'survey') {
            $relationType = 'UserSurvey';
        } elseif ($context == 'questionnaire') {
            $relationType = 'UserQuestionnaire';
        }

        $user = \Application\Model\User::getCurrentUser();
        $userId = $user ? $user->getId() : 0;
        $defaultRole = $user ? 'member' : 'anonymous';

        return "
            JOIN Application\Model\\$relationType relation WITH relation.$context = survey AND relation.user = $userId
            JOIN Application\Model\Role role WITH relation.role = role OR role.name = '$defaultRole'
            JOIN Application\Model\Permission permission WITH permission MEMBER OF role.permissions AND permission.name = '$permission'
";
    }

}
