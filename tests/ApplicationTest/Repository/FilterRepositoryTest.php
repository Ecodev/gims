<?php

namespace ApplicationTest\Repository;

class FilterRepositoryTest extends AbstractRepository
{

    public function testGetOneByNames()
    {
        $repository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        $c1 = new \Application\Model\Filter('parent filter');
        $c2 = new \Application\Model\Filter('child filter');
        $c1->setIsOfficial(true);
        $c2->setIsOfficial(true);
        $c1->addChild($c2);

        $this->getEntityManager()->persist($c1);
        $this->getEntityManager()->persist($c2);
        $this->getEntityManager()->flush();

        $this->assertNull($repository->getOneOfficialByNames('non existing filter', null), 'return null if not found');
        $this->assertNull($repository->getOneOfficialByNames('non existing filter', 'non existing parent filter'), 'return null if not found');

        $this->assertSame($c1, $repository->getOneOfficialByNames($c1->getName(), null), 'return parent filter');
        $this->assertNull($repository->getOneOfficialByNames($c1->getName(), 'non existing parent filter'), 'return null if parent does not match');

        $this->assertSame($c2, $repository->getOneOfficialByNames($c2->getName(), $c1->getName()), 'return parent filter');
        $this->assertNull($repository->getOneOfficialByNames($c2->getName(), null), 'return null if no parent given');
        $this->assertNull($repository->getOneOfficialByNames($c2->getName(), 'non existing parent filter'), 'return null if parent does not match');
    }

}
