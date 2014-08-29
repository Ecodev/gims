<?php

namespace Application\Repository;

class RoleRepository extends AbstractRepository
{

    /**
     * Returns all roles except built-in roles
     * @param string $action
     * @param string $search
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('role');

        // Never list built-in roles, because they should not be used by end-user to define permissions
        $qb->where('role.name NOT IN(:roles)');
        $qb->setParameter('roles', ['anonymous', 'member']);

        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
    }

    /**
     * cache of roles [userId => [contextType => [contextId => roleName]]]
     * @var array
     */
    private $cache = [];

    /**
     * Get all role names the user has in the given contexts
     * This is optimized for mass query on a user basis and cached in memory.
     * @param \Application\Model\User $user
     * @param \Application\Service\RoleContextInterface $contexts
     * @return string[]
     * @throws \Exception
     */
    public function getAllRoleNames(\Application\Model\User $user, \Application\Service\RoleContextInterface $contexts = null)
    {
        // If we are here, it means there is at least a logged in user,
        // which means he has, at the very least, the hardcoded role of member
        $roleNames = array('member');

        // If the user is not persisted yet, there is no way to have any other roles than member
        if (!$user->getId()) {
            return $roleNames;
        }

        // Fill cache if needed
        if (!isset($this->cache[$user->getId()])) {
            $this->fillCache($user);
        }

        // If no context at all, return all role names
        if (is_null($contexts)) {
            return $this->cache[$user->getId()]['all'];
        }

        // Ensure we have something foreach-able
        if (!$contexts instanceof \Application\Service\MultipleRoleContext) {
            $contexts = [$contexts];
        }

        // Picks the roles from cache according to context
        foreach ($contexts as $context) {

            if ($context instanceof \Application\Model\Survey) {
                $type = 'survey';
            } elseif ($context instanceof \Application\Model\Questionnaire) {
                $type = 'questionnaire';
            } elseif ($context instanceof \Application\Model\FilterSet) {
                $type = 'filterSet';
            } elseif ($context instanceof \Application\Service\MissingRequiredRoleContext) {
                $type = null;
            } else {
                throw new \Exception('Unsupported role context type: ' . get_class($context));
            }

            if ($type && isset($this->cache[$user->getId()][$type][$context->getId()])) {
                $roleNames = array_merge($roleNames, $this->cache[$user->getId()][$type][$context->getId()]);
            }
        }

        return $roleNames;
    }

    /**
     * Fills the cache of user roles for the given user
     * @param \Application\Model\User $user
     */
    private function fillCache(\Application\Model\User $user)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('type', 'type');
        $rsm->addScalarResult('id', 'id');

        $qb = $this->getEntityManager()->createNativeQuery("
                SELECT role.name AS name, 'survey' AS type, survey_id AS id FROM role INNER JOIN user_survey ON (user_id = :user AND role.id = role_id)
                UNION
                SELECT role.name AS name, 'questionnaire' AS type, questionnaire_id AS id FROM role INNER JOIN user_questionnaire ON (user_id = :user AND role.id = role_id)
                UNION
                SELECT role.name AS name, 'filterSet' AS type, filter_set_id AS id FROM role INNER JOIN user_filter_set ON (user_id = :user AND role.id = role_id)
            ", $rsm);

        $qb->setParameter('user', $user);
        $result = $qb->getResult();

        $this->cache[$user->getId()] = [
            'survey' => [],
            'questionnaire' => [],
            'filterSet' => [],
        ];

        $all = [];
        foreach ($result as $item) {
            $all[] = $item['name'];
            if (!isset($this->cache[$user->getId()][$item['type']][$item['id']])) {
                $this->cache[$user->getId()][$item['type']][$item['id']] = [];
            }
            $this->cache[$user->getId()][$item['type']][$item['id']][] = $item['name'];
        }

        $this->cache[$user->getId()]['all'] = array_unique($all);
    }

}
