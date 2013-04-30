<?php

namespace ApplicationTest\Repository;

class FilterRepositoryTest extends AbstractRepository
{

    public function testGetOneByNames()
    {
        $repository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        $c1 = new \Application\Model\Filter('parent filter');
        $c2 = new \Application\Model\Filter('child filter');
        $c1->addChild($c2);

        $this->getEntityManager()->persist($c1);
        $this->getEntityManager()->persist($c2);
        $this->getEntityManager()->flush();

        $this->assertNull($repository->getOneByNames('non existing filter', null), 'return null if not found');
        $this->assertNull($repository->getOneByNames('non existing filter', 'non existing parent filter'), 'return null if not found');

        $this->assertSame($c1, $repository->getOneByNames($c1->getName(), null), 'return parent filter');
        $this->assertNull($repository->getOneByNames($c1->getName(), 'non existing parent filter'), 'return null if parent does not match');

        $this->assertSame($c2, $repository->getOneByNames($c2->getName(), $c1->getName()), 'return parent filter');
        $this->assertNull($repository->getOneByNames($c2->getName(), null), 'return null if no parent given');
        $this->assertNull($repository->getOneByNames($c2->getName(), 'non existing parent filter'), 'return null if parent does not match');
    }

}
