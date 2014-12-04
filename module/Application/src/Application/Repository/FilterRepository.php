<?php

namespace Application\Repository;

use Application\Utility;

class FilterRepository extends AbstractRepository
{

    private $cacheDescendants = array();

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('filter')
                   ->orderBy('filter.sorting')
                   ->addOrderBy('filter.id');

        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
    }

    /**
     * Return filters that have no parents
     * @return array
     */
    public function getRootFilters ()
    {
        $qb = $this->createQueryBuilder('filter')
                   ->orderBy('filter.sorting')
                   ->addOrderBy('filter.id');

        $this->addRootRestriction($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $qb
     * @return
     */
    private function addRootRestriction($qb)
    {
        $qb->leftJoin('filter.parents', 'parents')
           ->having('COUNT(parents.id) = 0')
           ->groupBy('filter.id');

        return $qb;
    }

    /**
     * Find all, sorting by given parameter, using id by default.
     * @param array $orderBy
     * @return array
     */
    public function findAll($orderBy = array('id' => 'ASC'))
    {
        return $this->findBy(array(), $orderBy);
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
        $qb = $this->createQueryBuilder('filter')->where('filter.name = :name');
        $parameters = array('name' => $name);
        if ($parentName) {
            $parameters['parentName'] = $parentName;
            $qb->join('filter.parents', 'parents', \Doctrine\ORM\Query\Expr\Join::WITH, 'parents.name = :parentName');
        } else {
            $this->addRootRestriction($qb);
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

    /**
     * Retrieve column names, short and long version for all filters and parts in 1 SQL query
     * @param \Application\Model\Filter[]|integer[] $filters
     * @param \Application\Model\Part[] $parts
     * @return array ['short' => short name, 'long' => long name]
     */
    public function getColumnNames($filters, $parts)
    {
        $query = $this->getEntityManager()->createQuery("SELECT
                filter.id AS filterId,
                filter.name AS name,
                filter.color AS filterColor,
                filter.sorting AS filterSorting,
                thematicFilter.id AS thematicId,
                thematicFilter.name AS thematic,
                thematicFilter.sorting AS thematicSorting,
                thematicFilter.color AS thematicColor
                FROM Application\Model\Filter filter
                LEFT JOIN filter.thematicFilter thematicFilter
                WHERE filter IN (:filters)");

        $params = array(
            'filters' => $filters,
        );

        $query->setParameters($params);
        $data = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $result = [];
        foreach ($data as $filter) {

            // Filter first letters of each word
            $filterAcronym = '';
            $words = preg_split('/\s/', $filter['name'], null, PREG_SPLIT_NO_EMPTY);
            foreach ($words as $word) {
                $filterAcronym .= substr($word, 0, 1);
            }

            foreach ($parts as $part) {
                $result[] = [
                    'field' => 'f' . $filter['filterId'] . 'p' . $part->getId(),
                    'part' => $part->getName(),
                    'partId' => $part->getId(),
                    'displayName' => $filterAcronym,
                    'displayLong' => $filter['name'],
                    'filterColor' => isset($filter['filterColor']) ? $filter['filterColor'] : Utility::getColor($filter['filterId'], 100),
                    'filterTextColor' => Utility::getLisibleColor(isset($filter['filterColor']) ? $filter['filterColor'] : Utility::getColor($filter['filterId'], 100)),
                    'thematic' => $filter['thematic'],
                    'thematicColor' => $filter['thematicColor'],
                    'thematicTextColor' => Utility::getLisibleColor(isset($filter['thematicColor']) ? $filter['thematicColor'] : Utility::getColor($filter['thematicId'], 100)),
                    'filterSorting' => $filter['filterSorting'],
                    'thematicSorting' => $filter['thematicSorting'],
                    'width' => 80
                ];
            }
        }

        usort($result, array($this, 'orderColumnsByThematicPartAndFilter'));

        return $result;
    }

    private function orderColumnsByThematicPartAndFilter($c1, $c2)
    {
        if ($c1['thematicSorting'] == $c2['thematicSorting']) {
            if ($c1['partId'] == $c2['partId']) {
                return $c1['filterSorting'] - $c2['filterSorting'];
            }

            return strcmp($c1['partId'], $c2['partId']);
        }

        return $c1['thematicSorting'] - $c2['thematicSorting'];
    }

}
