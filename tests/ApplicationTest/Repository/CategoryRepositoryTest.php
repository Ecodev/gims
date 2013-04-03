<?php

namespace ApplicationTest\Repository;

class CategoryRepositoryTest extends AbstractRepository
{

    public function testGetOneByNames()
    {
        $repository = $this->getEntityManager()->getRepository('Application\Model\Category');

        $c1 = new \Application\Model\Category('parent category');
        $c2 = new \Application\Model\Category('child category');
        $c2->setParent($c1);

        $this->getEntityManager()->persist($c1);
        $this->getEntityManager()->persist($c2);
        $this->getEntityManager()->flush();

        $this->assertNull($repository->getOneByNames('non existing category', null), 'return null if not found');
        $this->assertNull($repository->getOneByNames('non existing category', 'non existing parent category'), 'return null if not found');

        $this->assertSame($c1, $repository->getOneByNames($c1->getName(), null), 'return parent category');
        $this->assertNull($repository->getOneByNames($c1->getName(), 'non existing parent category'), 'return null if parent does not match');

        $this->assertSame($c2, $repository->getOneByNames($c2->getName(), $c1->getName()), 'return parent category');
        $this->assertNull($repository->getOneByNames($c2->getName(), null), 'return null if no parent given');
        $this->assertNull($repository->getOneByNames($c2->getName(), 'non existing parent category'), 'return null if parent does not match');
    }

}
