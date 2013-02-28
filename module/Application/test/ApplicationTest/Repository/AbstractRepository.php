<?php

namespace ApplicationTest\Repository;

use Zend\Db\ResultSet\ResultSet;
use PHPUnit_Framework_TestCase;

abstract class AbstractRepository extends PHPUnit_Framework_TestCase
{
    use \ApplicationTest\Traits\TestWithTransaction;
}
