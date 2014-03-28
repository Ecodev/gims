<?php

namespace Application\Repository;

class FilterRepository extends AbstractRepository
{

    use Traits\OrderedByName;

    private $cacheDescendants = array();

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null)
    {
        return $this->findAll();
    }

    /**
     * Returns one filter by name
     *
     * @param string $name
     * @param string $parentName
     *
     * @return \Application\Model\Filter
     */
    public function getOneByNames($name, $parentName)
    {
        $qb = $this->createQueryBuilder('f')->where('f.name = :name');
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
     * @param string $descendantType "summands" or "children"
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

        if (isset($this->cacheDescendants[$filterId][$descendantType])) {
            return $this->cacheDescendants[$filterId][$descendantType];
        } else {
            return array();
        }
    }

}
