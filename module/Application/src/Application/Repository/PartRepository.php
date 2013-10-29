<?php

namespace Application\Repository;

class PartRepository extends AbstractRepository
{

    /**
     * Override parent to order by Id
     * @return type
     */
    public function findAll()
    {
        return $this->findBy(array(), array('id' => 'ASC'));
    }

    /**
     * Returns a part either from database, or newly created
     * @param string $name
     * @return \Application\Model\Part
     */
    public function getOrCreate($name)
    {
        $part = $this->findOneByName($name);

        if (!$part) {
            $part = new \Application\Model\Part($name);
            $this->getEntityManager()->persist($part);
        }

        return $part;
    }

    /**
     * Return an array of IDs for all part which are not total
     * @return array
     */
    public function getIdsNonTotal()
    {

        $query = $this->getEntityManager()->createQuery('SELECT p.id
            FROM Application\Model\Part p
            WHERE
            p.isTotal = false'
        );

        $ids = array();
        foreach ($query->getArrayResult() as $data) {
            $ids[] = $data['id'];
        }

        return $ids;
    }

}
