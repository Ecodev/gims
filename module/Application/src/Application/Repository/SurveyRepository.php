<?php

namespace Application\Repository;

class SurveyRepository extends AbstractRepository
{

    public function findAll()
    {
        return $this->findBy(array(), array('year' => 'DESC', 'name' => 'ASC'));
    }

}
