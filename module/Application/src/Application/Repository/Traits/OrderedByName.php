<?php

namespace Application\Repository\Traits;

/**
 * Default queries to use order by name
 */
trait OrderedByName
{

    public function findAll()
    {
        return $this->findBy(array(), array('name' => 'ASC'));
    }

}
