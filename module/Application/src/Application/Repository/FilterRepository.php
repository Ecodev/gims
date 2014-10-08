<?php

namespace Application\Repository;

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
                ->orderBy('filter.id');

        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
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

    /**
     * Retrieve column names, short and long version for all filters and parts in 1 SQL query
     * @param \Application\Model\Filter[]|integer[] $filters
     * @param \Application\Model\Part[] $parts
     * @return array ['short' => short name, 'long' => long name]
     */
    public function getColumnNames($filters, $parts)
    {
        $query = $this->getEntityManager()
                ->createQuery("SELECT filter.id AS id, filter.name AS name, thematicFilter.name AS thematic
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

            // Thematic
            $thematicFirstLetter = '';
            $thematicFirstWord = '';
            if ($filter['thematic']) {
                $thematicFirstLetter = substr($filter['thematic'], 0, 1);
                $thematicFirstWord = preg_split('/\s/', $filter['thematic'], null, PREG_SPLIT_NO_EMPTY)[0] . ', ';
            }

            // Filter first letters of each word
            $filterAcronym = '';
            $words = preg_split('/\s/', $filter['name'], null, PREG_SPLIT_NO_EMPTY);
            foreach ($words as $word) {
                $filterAcronym .= substr($word, 0, 1);
            }

            foreach ($parts as $part) {
                $partFirstLetter = substr($part->getName(), 0, 1);
                $result[$filter['id']][$part->getId()] = [
                    'short' => strtoupper($thematicFirstLetter . $partFirstLetter . $filterAcronym),
                    'long' => $thematicFirstWord . $part->getName() . ', ' . $filter['name'],
                ];
            }
        }

        return $result;
    }

}
