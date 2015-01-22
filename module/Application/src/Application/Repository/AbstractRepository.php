<?php

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

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
     *
     * Here there are 3 different cases to consider:
     *   1. published objects are always accessible via DQL exception (like questionnaire)
     *   2. default roles of anonymous and member may give access to objects even without specific rights on them
     *   3. specific rights applied to one specific object, via one ore more contexts
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string|string[] $contexts
     * @param string $permission
     * @param null|string $exceptionDql a condition expressed in DQL to bypass all security check. This should almost never be used !
     * @throws Exception
     */
    protected function addPermission(QueryBuilder $qb, $contexts, $permission, $exceptionDql = null)
    {
        $whereParts = [];

        // 1. published objects
        if ($exceptionDql) {
            $whereParts[] = '(' . $exceptionDql . ')';
        }

        // 2. default roles of anonymous and member
        $whereParts[] = "
            EXISTS (
                SELECT roleDefaultRole.id FROM Application\Model\Role roleDefaultRole
                INNER JOIN Application\Model\Permission permissionDefaultRole WITH permissionDefaultRole MEMBER OF roleDefaultRole.permissions AND permissionDefaultRole.name = :permissionPermission
                WHERE roleDefaultRole.name = :permissionDefaultRole
            )";

        // 3. specific rights
        $contexts = (array) $contexts;
        foreach ($contexts as $i => $context) {
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

            $whereParts [] = "
                EXISTS (
                    SELECT relation$i.id FROM Application\Model\\$relationType relation$i
                    INNER JOIN Application\Model\Role role$i WITH relation$i.role = role$i
                    INNER JOIN Application\Model\Permission permission$i WITH permission$i MEMBER OF role$i.permissions AND permission$i.name = :permissionPermission
                    WHERE relation$i.user = :permissionUser AND relation$i.$context = $context.id
                )";
        }

        $qb->andWhere("(" . implode(' OR ', $whereParts) . ")");

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
            $fields = array_intersect($existingFields, ['code', 'name']);
            $fields = array_map(function ($field) use ($alias) {
                return $alias . '.' . $field;
            }, $fields);
        }

        // Build the WHERE clause
        $wordWheres = [];
        foreach (preg_split('/[[:space:]]+/', $search, -1, PREG_SPLIT_NO_EMPTY) as $i => $word) {
            $parameterName = 'searchWord' . $i;

            $fieldWheres = [];
            foreach ($fields as $field) {
                $fieldWheres[] = 'LOWER(CAST(' . $field . ' AS text)) LIKE LOWER(:' . $parameterName . ')';
            }

            if ($fieldWheres) {
                $wordWheres[] = '(' . implode(' OR ', $fieldWheres) . ')';
                $qb->setParameter($parameterName, '%' . $word . '%');
            }
        }

        if ($wordWheres) {
            $qb->andWhere('(' . implode(' AND ', $wordWheres) . ')');
        }
    }

}
