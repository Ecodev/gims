<?php

namespace ApplicationTest\Service;

class HydratorTest extends \ApplicationTest\Controller\AbstractController
{

    /**
     * @var \Application\Model\User
     */
    private $user;

    /**
     * @var \Application\Service\Hydrator
     */
    private $hydrator;

    public function setUp()
    {
        parent::setUp();

        $this->user = new \Application\Model\User();
        $this->user->setName('John');
        $this->hydrator = new \Application\Service\Hydrator();
    }

    public function testCanHydrateAndExtract()
    {
        $data = array(
            'name' => 'John Connor',
            'email' => 'john.connor@skynet.net',
        );

        $this->hydrator->hydrate($data, $this->user);

        $this->assertEquals($data['name'], $this->user->getName());
        $this->assertEquals($data['email'], $this->user->getEmail());

        $actual = $this->hydrator->extract($this->user, array('name', 'email'));
        unset($actual['id']);
        $this->assertEquals($data, $actual, 'it must be exactly same as input, except the id');
    }

    public function testCanHydrateRecursive()
    {
        $filter = new \Application\Model\Filter();
        $filter->setOfficialFilter($filter);

        $data = array(
            'name' => 'original name',
            'officialFilter' => array(
                'id' => 12345,
                'name' => 'this should not overwrite the original name',
            ),
        );

        $this->hydrator->hydrate($data, $filter);
        $this->assertEquals('original name', $filter->getName());
    }

    public function testHydrateUnkownPropertiesFailSilently()
    {

        $data = array(
            'foo' => 'bar',
            'name' => 'John Connor',
        );

        $this->hydrator->hydrate($data, $this->user);
        $this->assertEquals($data['name'], $this->user->getName());
        $this->assertArrayNotHasKey('foo', $this->hydrator->extract($this->user, array('foo')));
    }

    public function testIdCannotBeHydrated()
    {
        $data = array(
            'id' => 12345,
        );

        $this->hydrator->hydrate($data, $this->user);
        $this->assertNull($this->user->getId());
    }

    public function testExtractArray()
    {
        $user2 = clone $this->user;
        $user2->setName('Bob');

        $this->assertEquals(array(
            0 =>
            array(
                'id' => null,
                'name' => 'John',
            ),
            1 =>
            array(
                'id' => null,
                'name' => 'Bob',
            ),
                )
                , $this->hydrator->extractArray(array($this->user, $user2), array('name')));
    }

    public function testExtractSubObject()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');
        $filter1->setOfficialFilter($filter2);

        $this->assertEquals(array(
            'id' => null,
            'name' => 'filter 1',
            'officialFilter' => array(
                'id' => null,
                'name' => 'filter 2',
            ),
                ), $this->hydrator->extract($filter1, array(
                    'name',
                    'officialFilter' => array('name'),
                    'unkown properties' => array('foo', 'bar')
                )), 'should return subobject, but only known one');
    }

    public function testExtractSubObjects()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');
        $filter1->addChild($filter2);

        $this->assertEquals(array(
            'id' => null,
            'name' => 'filter 1',
            'children' => array(
                array(
                    'id' => null,
                    'name' => 'filter 2',
                ),
            ),
                ), $this->hydrator->extract($filter1, array(
                    'name',
                    'children' => array('name'),
                )), 'should return array of subobjects, but only known ones');
    }

    public function testExtractSubObjectWithRecursiveConfiguration()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');
        $filter1->setOfficialFilter($filter2);

        $this->assertEquals(array(
            'id' => null,
            'name' => 'filter 1',
            'officialFilter' => array(
                'id' => null,
                'name' => 'filter 2',
                'officialFilter' => null,
            ),
                ), $this->hydrator->extract($filter1, array(
                    'name',
                    'officialFilter' => '__recursive',
                )), 'should return same properties for children and parent');
    }

    public function testExtractBooleanProperty()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter1->setIsOfficial(true);

        $this->assertEquals(array(
            'id' => null,
            'isOfficial' => true,
                ), $this->hydrator->extract($filter1, array(
                    'isOfficial',
                )), 'should use correct getter for boolean properties');
    }

    public function testExtractWithClosure()
    {
        $this->assertEquals(array(
            'id' => null,
            'custom name' => 'Mr. John Connor',
                ), $this->hydrator->extract($this->user, array(
                    'custom name' => function(\Application\Service\Hydrator $hydrator, \Application\Model\User $user) {
                        return 'Mr. ' . $user->getName() . ' Connor';
                    },
                )), 'should allow custom properties via Closure');
    }

    public function testExtractDateTimeAsString()
    {
        $this->user->timestampCreation();
        $this->assertEquals(array(
            'id' => null,
            'dateCreated' => $this->user->getDateCreated()->format(\DateTime::ISO8601),
                ), $this->hydrator->extract($this->user, array(
                    'dateCreated',
                )), 'should serialize DateTime');
    }

    public function testHydrateSubObject()
    {
        $filter1 = new \Application\Model\Filter('filter 1');
        $filter2 = new \Application\Model\Filter('filter 2');

        // Create a stub for the \Application\Service\Hydrator class, so we don't have to mess with database
        $mockHydrator = $this->getMock('\Application\Service\Hydrator', array('getObject'), array(), '', false);
        $mockHydrator->expects($this->any())
                ->method('getObject')
                ->will($this->returnValue($filter2));

        $mockHydrator->hydrate(array(
            'officialFilter' => array(
                'id' => 12345,
            ),
                ), $filter1);
        $this->assertEquals($filter2, $filter1->getOfficialFilter(), 'can set subobject');

        $filter1->setOfficialFilter(null);
        $mockHydrator->hydrate(array('officialFilter' => 12345), $filter1);
        $this->assertEquals($filter2, $filter1->getOfficialFilter(), 'can also use short syntax with only ID');
    }

}
