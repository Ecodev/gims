<?php

namespace ApplicationTest\Service;

class HydratorTest extends \ApplicationTest\Controller\AbstractController
{

    public function testCanHydrateAndExtract()
    {
        $user = new \Application\Model\User();
        $hydrator = new \Application\Service\Hydrator();

        $data = array(
            'name' => 'John Connor',
            'email' => 'john.connor@skynet.net',
        );

        $hydrator->hydrate($data, $user);

        $this->assertEquals($data['name'], $user->getName());
        $this->assertEquals($data['email'], $user->getEmail());

        $actual = $hydrator->extract($user, array('name', 'email'));
        unset($actual['id']);
        $this->assertEquals($data, $actual, 'it must be exactly same as input, except the id');
    }

    public function testCanHydrateRecursive()
    {
        $filter = new \Application\Model\Filter();
        $filter->setOfficialFilter($filter);

        // Create a stub for the \Application\Service\Hydrator class, so we don't have to mess with database
        $hydrator = $this->getMock('\Application\Service\Hydrator', array('getObject'), array(), '', false);
        $hydrator->expects($this->any())
                ->method('getObject')
                ->will($this->returnValue($filter));

        $data = array(
            'name' => 'original name',
            'officialFilter' => array(
                'id' => 12345,
                'name' => 'this should not overwrite the original name',
            ),
        );

        $hydrator->hydrate($data, $filter);
        $this->assertEquals('original name', $filter->getName());
    }

    public function testUnkownPropertiesFailSilently()
    {
        $user = new \Application\Model\User();
        $hydrator = new \Application\Service\Hydrator();

        $data = array(
            'foo' => 'bar',
            'name' => 'John Connor',
        );

        $hydrator->hydrate($data, $user);
        $this->assertEquals($data['name'], $user->getName());
        $this->assertArrayNotHasKey('foo', $hydrator->extract($user, array('foo')));
    }

    public function testIdCannotBeHydrated()
    {
        $user = new \Application\Model\User();
        $hydrator = new \Application\Service\Hydrator();

        $data = array(
            'id' => 12345,
        );

        $hydrator->hydrate($data, $user);
        $this->assertNull($user->getId());
    }

}
