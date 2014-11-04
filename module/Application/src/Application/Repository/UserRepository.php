<?php

namespace Application\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

class UserRepository extends AbstractRepository
{

    use Traits\OrderedByName;

    /**
     * Return statistics for the specified user
     * Currently it's the count questionnaire by status, but it could be more info
     * @param \Application\Model\User $user
     * @return array
     */
    public function getStatistics(\Application\Model\User $user)
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
     * Return all
     * @param $geonames
     * @param $roles
     * @param $types
     * @internal param $rolesByGeoname
     * @return array
     */
    public function getAllHavingRoles($geonames, $roles, $types)
    {
        $selectModel = "SELECT distinct u.id as user_id, u.email as user_email, u.name as user_name, q.id as questionnaire_id, g.id as geoname_id, s.id as survey_id, r.id as role_id FROM \"user\" as u ";

        $sql = $selectModel;
        $sql .= "left join user_survey us on us.user_id = u.id ";
        $sql .= "left join survey s on us.survey_id = s.id ";
        $sql .= "left join questionnaire q on q.survey_id = s.id ";
        $sql .= "left join role r on r.id = us.role_id ";
        $sql .= "left join geoname g on q.geoname_id = g.id ";
        $sql .= "WHERE us.role_id IN (" . $roles . ") AND g.id IN (" . $geonames . ") AND s.type IN (" . $types . ") ";

        $sql .= "UNION ";

        $sql .= $selectModel;
        $sql .="left join user_survey us on us.user_id = u.id ";
        $sql .= "left join survey s on us.survey_id = s.id ";
        $sql .= "left join questionnaire q on q.survey_id = s.id ";
        $sql .= "left join role r on r.id = us.role_id ";
        $sql .= "left join geoname g on q.geoname_id = g.id ";
        $sql .= "WHERE us.role_id in (" . $roles . ")  AND g.id in (" . $geonames . ") AND s.type IN (" . $types . ") ";

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();

        $rsm->addScalarResult('user_id', 'user_id');
        $rsm->addScalarResult('user_email', 'user_email');
        $rsm->addScalarResult('user_name', 'user_name');
        $rsm->addScalarResult('questionnaire_id', 'questionnaire_id');
        $rsm->addScalarResult('geoname_id', 'geoname_id');
        $rsm->addScalarResult('survey_id', 'survey_id');
        $rsm->addScalarResult('role_id', 'role_id');

        $qb = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        $result = $qb->getResult();

        return $result;
    }

}
