<?php

namespace Application\Repository;

class FilterRepository extends AbstractRepository
{

    private $cache = array();

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

    public function getSummandIds($filterId)
    {
        return $this->getDescendantIds($filterId, 'summands');
    }

    public function getChildrenIds($filterId)
    {
        return $this->getDescendantIds($filterId, 'children');
    }

    protected function getDescendantIds($filterId, $descendantType)
    {
        if (!$this->cache) {

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
                $this->cache[$filter['id']] = $descendants;
            }
        }

        return @$this->cache[$filterId][$descendantType] ? : array();
    }

}

