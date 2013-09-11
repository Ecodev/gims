<?php

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

abstract class AbstractRepository extends EntityRepository
{

    /**
     * Returns all items with permissions
     * @param string $action
     * @return array
     */
    public function getAllWithPermission($action = 'read')
    {
        return $this->findAll();
    }

    /**
     * Modify $qb to add constraint to check for given permission in the current context.
     *
     * This method assumes that the member $context already exists in the query, so you
     * need to do appropriate join before using this method.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $context
     * @param string $permission
     * @throws Exception
     */
    protected function addPermission(QueryBuilder $qb, $context, $permission)
    {
        if ($context == 'survey') {
            $relationType = 'UserSurvey';
        } elseif ($context == 'questionnaire') {
            $relationType = 'UserQuestionnaire';
        } else {
            throw new Exception("Unsupported context '$context' for automatic permission");
        }

        $qb->leftJoin("Application\Model\\$relationType", 'relation', Join::WITH, "relation.$context = $context AND relation.user = :permissionUser");
        $qb->join('Application\Model\Role', 'role', Join::WITH, "relation.role = role OR role.name = :permissionDefaultRole");
        $qb->join('Application\Model\Permission', 'permission', Join::WITH, "permission MEMBER OF role.permissions AND permission.name = :permissionPermission");

        $user = \Application\Model\User::getCurrentUser();
        $defaultRole = $user ? 'member' : 'anonymous';

        $qb->setParameter('permissionUser', $user);
        $qb->setParameter('permissionDefaultRole', $defaultRole);
        $qb->setParameter('permissionPermission', $permission);
    }

}
