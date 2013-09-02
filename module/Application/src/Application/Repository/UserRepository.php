<?php

namespace Application\Repository;

class UserRepository extends AbstractRepository
{

    use Traits\OrderedByName;

    /**
     * Return statistics for the specified user
     * Currently it's the count questionnaire by status, but it could be more info
     * @param \Application\Model\User $user
     * @return array
     */
    public function getStatistics(\Application\Model\User $user = null)
    {

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('total', 'total');

        $counts = array('COUNT(*) AS total');
        foreach (\Application\Model\QuestionnaireStatus::getValues() as $status) {
            $status = (string) $status;
            $rsm->addScalarResult($status, $status);
            $counts[] = "COUNT(CASE WHEN status = '$status' THEN TRUE ELSE NULL END) AS $status";
        }

        $sql = "SELECT " . join(', ', $counts) . " FROM questionnaire";
        $quey = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $result = $quey->getSingleResult();

        return $result;
    }

}
