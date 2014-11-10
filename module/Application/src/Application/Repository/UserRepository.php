<?php

namespace Application\Repository;

use Application\Model\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

class UserRepository extends AbstractRepository
{

    use Traits\OrderedByName;

    /**
     * Return statistics for the specified user
     * Currently it's the count questionnaire by status, but it could be more info
     * @param User $user
     * @return array
     */
    public function getStatistics(User $user)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('total', 'total');

        $counts = array('COUNT(*) AS total');
        foreach (\Application\Model\QuestionnaireStatus::getValues() as $status) {
            $status = (string) $status;
            $rsm->addScalarResult($status, $status);
            $counts[] = "COUNT(CASE WHEN status = '$status' THEN TRUE ELSE NULL END) AS $status";
        }

        $questionnaireRepository = $this->getEntityManager()
                                        ->getRepository('Application\Model\Questionnaire');
        $questionnaires = $questionnaireRepository->getAllWithPermission();

        $sql = "SELECT " . join(', ', $counts) . " FROM questionnaire WHERE id IN (:questionnaires)";

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('questionnaires', $questionnaires);
        $result = $query->getSingleResult();

        return $result;
    }

    /**
     * Return all users with the permission on the given object
     * @param \Application\Service\RoleContextInterface $context
     * @param string $permission
     * @return array
     * @throws Exception
     */
    public function getAllHavingPermission(\Application\Service\RoleContextInterface $context, $permission)
    {
        if ($context instanceof \Application\Model\Survey) {
            $relationType = 'UserSurvey';
            $context = 'survey';
        } elseif ($context instanceof \Application\Model\Questionnaire) {
            $relationType = 'UserQuestionnaire';
            $context = 'questionnaire';
        } else {
            throw new Exception("Unsupported context for automatic permission");
        }

        $qb = $this->createQueryBuilder('user');
        $qb->leftJoin("Application\Model\\$relationType", 'relation', Join::WITH, "relation.$context = $context AND relation.user = :permissionUser");
        $qb->join('Application\Model\Role', 'role', Join::WITH, "relation.role = role OR role.name = 'member'");
        $qb->join('Application\Model\Permission', 'permission', Join::WITH, "permission MEMBER OF role.permissions AND permission.name = :permissionPermission");

        $qb->setParameter('permissionPermission', $permission);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     * @param string $action
     * @param string $search
     * @param string $parentName
     * @param \Application\Model\AbstractModel $parent
     * @return User[]
     */
    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('user');

        if ($parent) {
            $qb->where($parentName . ' = :parent');
            $qb->setParameter('parent', $parent);
        }

        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
    }

    /**
     * If user param is null, return all users that have at least one of the given roles on questionnaires (or survey by inheritance)
     * If user params is not null, limit returned objects to those in relation with given user, and add detail : questionnaires and roles by questionnaire
     * @param integer[] $geonames ids
     * @param integer[] $roles
     * @param string[] $types
     * @param User $user optional, if used, return only data relative to user
     * @return array
     */
    public function getAllHavingRoles(array $geonames, array $roles, array $types, User $user = null)
    {
        $selectModel = "SELECT distinct u.id as user_id, u.email as user_email, u.name as user_name ";

        if ($user) {
            $selectModel .= ", q.id as questionnaire_id, ";
            $selectModel .= "g.id as geoname_id, ";
            $selectModel .= "g.name as geoname_name, ";
            $selectModel .= "s.id as survey_id, ";
            $selectModel .= "s.code as survey_code, ";
            $selectModel .= "r.id as role_id, ";
            $selectModel .= "r.name as role_name, ";
            $selectModel .= "relation.id as relation_id, ";
            $selectModel .= "creator.name as relation_creator_name, ";
            $selectModel .= "creator.email as relation_creator_email, ";
            $selectModel .= "modifier.name as relation_modifier_name, ";
            $selectModel .= "modifier.email as relation_modifier_email, ";
        }
        $sql = $selectModel;
        if ($user) {
            $sql .= "'user_survey' as relation_type ";
        }
        $sql .= "FROM \"user\" as u ";
        $sql .= "left join user_survey relation on relation.user_id = u.id ";
        $sql .= "left join survey s on relation.survey_id = s.id ";
        $sql .= "left join questionnaire q on q.survey_id = s.id ";
        $sql .= "left join role r on r.id = relation.role_id ";
        $sql .= "left join \"user\" creator on creator.id = relation.creator_id ";
        $sql .= "left join \"user\" modifier on modifier.id = relation.modifier_id ";
        $sql .= "left join geoname g on q.geoname_id = g.id ";
        $sql .= "WHERE relation.role_id IN (:roles) AND g.id IN (:geonames) AND s.type IN (:types) ";
        if ($user) {
            $sql .= " AND u.id = :userId ";
        } else {
            $sql .= " AND u.id <> :userId ";
        }

        $sql .= "UNION ";

        $sql .= $selectModel;
        if ($user) {
            $sql .= "'user_questionnaire' as relation_type ";
        }
        $sql .= "FROM \"user\" as u ";
        $sql .= "left join user_questionnaire relation on relation.user_id = u.id ";
        $sql .= "left join questionnaire q on relation.questionnaire_id = q.id ";
        $sql .= "left join role r on r.id = relation.role_id ";
        $sql .= "left join \"user\" creator on creator.id = relation.creator_id ";
        $sql .= "left join \"user\" modifier on modifier.id = relation.modifier_id ";
        $sql .= "left join geoname g on q.geoname_id = g.id ";
        $sql .= "left join survey s on s.id = q.survey_id ";
        $sql .= "WHERE relation.role_id IN (:roles) AND g.id IN (:geonames) AND s.type IN (:types) ";
        if ($user) {
            $sql .= " AND u.id = :userId ";
        } else {
            $sql .= " AND u.id <> :userId ";
        }

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();

        $rsm->addScalarResult('user_id', 'user_id');
        $rsm->addScalarResult('user_email', 'user_email');
        $rsm->addScalarResult('user_name', 'user_name');

        if ($user) {
            $rsm->addScalarResult('questionnaire_id', 'questionnaire_id');
            $rsm->addScalarResult('geoname_id', 'geoname_id');
            $rsm->addScalarResult('geoname_name', 'geoname_name');
            $rsm->addScalarResult('survey_id', 'survey_id');
            $rsm->addScalarResult('survey_code', 'survey_code');
            $rsm->addScalarResult('role_id', 'role_id');
            $rsm->addScalarResult('role_name', 'role_name');
            $rsm->addScalarResult('relation_id', 'relation_id');
            $rsm->addScalarResult('relation_creator', 'relation_creator');
            $rsm->addScalarResult('relation_type', 'relation_type');
            $rsm->addScalarResult('relation_creator_name', 'relation_creator_name');
            $rsm->addScalarResult('relation_creator_email', 'relation_creator_email');
            $rsm->addScalarResult('relation_modifier_name', 'relation_modifier_name');
            $rsm->addScalarResult('relation_modifier_email', 'relation_modifier_email');
        }

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        $query->setParameter('geonames', $geonames);
        $query->setParameter('roles', $roles);
        $query->setParameter('types', $types);
        if ($user) {
            $query->setParameter('userId', $user->getId());
        } else {
            $query->setParameter('userId', User::getCurrentUser()->getId());
        }

        $result = $query->getResult();

        return $result;
    }

}
