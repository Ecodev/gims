<?php

namespace Application\Repository;

class FilterRepository extends AbstractRepository
{

    private $cacheDescendants = array();
    private $cacheUnofficialNames = array();

    use Traits\OrderedByName;

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($action = 'read')
    {
        return $this->findAll();
    }

    /**
     * Returns one official filter
     *
     * @param string $name
     * @param string $parentName
     *
     * @return \Application\Model\Filter
     */
    public function getOneOfficialByNames($name, $parentName)
    {
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        $qb = $filterRepository->createQueryBuilder('f')->where('f.name = :name AND f.questionnaire IS NULL');
        $parameters = array('name' => $name);
        if ($parentName) {
            $parameters['parentName'] = $parentName;
            $qb->join('f.parents', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.name = :parentName');
        } else {
            $qb->leftJoin('f.parents', 'p')
                    ->having('COUNT(p.id) = 0')
                    ->groupBy('f.id');
        }

        $q = $qb->getQuery();
        $q->setParameters($parameters);

        $filter = $q->getOneOrNullResult();

        return $filter;
    }

    public function getOfficialRoots()
    {
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        $qb = $filterRepository->createQueryBuilder('f')->where('f.questionnaire IS NULL');
        $qb->leftJoin('f.parents', 'p');

        $q = $qb->getQuery();

        $filter = $q->getResult();

        return $filter;
    }

    /**
     * Returns an array of ID of summands
     * @param integer $filterId
     * @return array
     */
    public function getSummandIds($filterId)
    {
        return $this->getDescendantIds($filterId, 'summands');
    }

    /**
     * Returns an array of ID of children
     * @param integer $filterId
     * @return array
     */
    public function getChildrenIds($filterId)
    {
        return $this->getDescendantIds($filterId, 'children');
    }

    /**
     * Returns an array of ID of descendants (either summands or children)
     * @param integer $filterId
     * @param string $descendantType
     * @return array
     */
    protected function getDescendantIds($filterId, $descendantType)
    {
        if (!$this->cacheDescendants) {

            $qb = $this->createQueryBuilder('filter')
                    ->select('filter, summands, children')
                    ->leftJoin('filter.summands', 'summands')
                    ->leftJoin('filter.children', 'children')
            ;

            // Restructure cache to be [questionnaireId => [filterId => [partId => value]]]
            $res = $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            foreach ($res as $filter) {

                $descendants = array(
                    'summands' => array(),
                    'children' => array(),
                );
                foreach (array_keys($descendants) as $type) {
                    foreach ($filter[$type] as $descendant) {
                        $descendants[$type][] = $descendant['id'];
                    }
                }
                $this->cacheDescendants[$filter['id']] = $descendants;
            }
        }

        return @$this->cacheDescendants[$filterId][$descendantType] ? : array();
    }

    /**
     * Return the unofficial name of an official filter for the given questionnaire
     * @param integer $officialFilterId
     * @param integer $questionnaireId
     * @return string|null
     */
    public function getUnofficialName($officialFilterId, $questionnaireId)
    {

        if (!isset($this->cacheUnofficialNames[$questionnaireId])) {

            $qb = $this->createQueryBuilder('filter')
                    ->select('officialFilter.id, filter.name')
                    ->join('filter.officialFilter', 'officialFilter')
                    ->andWhere('filter.questionnaire = :questionnaire')
            ;

            $qb->setParameter('questionnaire', $questionnaireId);

            // Restructure cache to be [questionnaireId => [officialFilterId => name]]
            $res = $qb->getQuery()->getResult();
            foreach ($res as $filter) {
                $this->cacheUnofficialNames[$questionnaireId][$filter['id']] = $filter['name'];
            }
        }

        return @$this->cacheUnofficialNames[$questionnaireId][$officialFilterId];
    }

}

