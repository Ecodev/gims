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
     * @param string $search
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('object');
        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
    }

    /**
     * Modify $qb to add constraint to check for given permission in the current context.
     * This method assumes that the member $context already exists in the query, so you
     * need to do appropriate join before using this method.
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $context
     * @param string $permission
     * @param null|string $exceptionDql a condition expressed in DQL to bypass all security check. This should almost never be used !
     * @throws Exception
     */
    protected function addPermission(QueryBuilder $qb, $context, $permission, $exceptionDql = null)
    {
        if ($context == 'survey') {
            $relationType = 'UserSurvey';
        } elseif ($context == 'questionnaire') {
            $relationType = 'UserQuestionnaire';
        } elseif ($context == 'filterSet') {
            $relationType = 'UserFilterSet';
        } elseif ($context == 'filter') {
            $relationType = 'UserFilter';
        } else {
            throw new \Exception("Unsupported context '$context' for automatic permission");
        }

        if ($exceptionDql) {
            $exceptionDql = '(' . $exceptionDql . ') OR ';
        }

        $qb->andWhere("($exceptionDql
            EXISTS (
                SELECT relation.id FROM Application\Model\\$relationType relation
                INNER JOIN Application\Model\Role role WITH relation.role = role OR role.name = :permissionDefaultRole
                INNER JOIN Application\Model\Permission permission WITH permission MEMBER OF role.permissions AND permission.name = :permissionPermission
                WHERE relation.user = :permissionUser AND relation.$context = $context.id
            )
        )");

        $user = \Application\Model\User::getCurrentUser();
        $defaultRole = $user ? 'member' : 'anonymous';

        $qb->setParameter('permissionUser', $user);
        $qb->setParameter('permissionDefaultRole', $defaultRole);
        $qb->setParameter('permissionPermission', $permission);
    }

    /**
     * Modify $qb to add a constraint to search for all words contained in $search.
     * This will search in a few hardcoded fields if they are available.
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $search
     * @param array $fields if standard fields are not enough, an array of table.field strings
     */
    protected function addSearch(QueryBuilder $qb, $search, array $fields = null)
    {
        if (!$search) {
            return;
        }

        // If no fields specified, auto-detect them
        if (!$fields) {
            $aliases = $qb->getRootAliases();
            $alias = reset($aliases);

            $existingFields = $this->getClassMetadata()->getFieldNames();
            $fields = array_intersect($existingFields, array('code', 'name'));
            $fields = array_map(function ($field) use ($alias) {
                return $alias . '.' . $field;
            }, $fields);
        }

        // Build the WHERE clause
        $wordWheres = array();
        foreach (preg_split('/[[:space:]]+/', $search, -1, PREG_SPLIT_NO_EMPTY) as $i => $word) {
            $parameterName = 'searchWord' . $i;

            $fieldWheres = array();
            foreach ($fields as $field) {
                $fieldWheres[] = 'LOWER(CAST(' . $field . ' AS text)) LIKE LOWER(:' . $parameterName . ')';
            }

            if ($fieldWheres) {
                $wordWheres[] = '(' . join(' OR ', $fieldWheres) . ')';
                $qb->setParameter($parameterName, '%' . $word . '%');
            }
        }

        if ($wordWheres) {
            $qb->andWhere('(' . join(' AND ', $wordWheres) . ')');
        }
    }

}
