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

    public function getAllNonTotal() {

        $query = $this->getEntityManager()->createQuery('SELECT p
            FROM Application\Model\Part p
            WHERE
            p.isTotal = false'
        );

        $parts = $query->getResult();

        return $parts;
    }
}
