<?php

namespace Application\Repository;

class CountryRepository extends AbstractRepository
{
    use Traits\OrderedByName;

    public function getAllWithPermission($action = 'read', $search = null)
    {
        return $this->findAll();
    }
}
