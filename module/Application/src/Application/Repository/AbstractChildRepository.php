<?php

namespace Application\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

abstract class AbstractChildRepository extends AbstractRepository
{

    public function getAllWithPermission($action = 'read', $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
            return $this->findBy(array($parentName => $parent));
    }

}
