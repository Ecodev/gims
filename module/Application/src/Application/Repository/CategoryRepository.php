<?php

namespace Application\Repository;

class CategoryRepository extends AbstractRepository
{

    /**
     * Returns a category either from database, or newly created
     * @param string $name
     * @param string $parentName
     * @return \Application\Model\Category
     */
    public function getOneByNames($name, $parentName)
    {
        $categoryRepository = $this->getEntityManager()->getRepository('Application\Model\Category');

        $qb = $categoryRepository->createQueryBuilder('c')->where('c.name = :name');
        $parameters = array('name' => $name);
        if ($parentName) {
            $parameters['parentName'] = $parentName;
            $qb->join('c.parent', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.name = :parentName');
        } else {
            $qb->andWhere('c.parent IS NULL');
        }

        $q = $qb->getQuery();
        $q->setParameters($parameters);

        $category = $q->getOneOrNullResult();

        return $category;
    }

}
