<?php

namespace Application\Repository;

class QuestionRepository extends AbstractRepository
{

    public function changeType ($id, $type)
    {
        $type = strtolower(str_replace("Application\\Model\\Question\\", "", $type));
        $sql = "UPDATE question set dtype='".$type."' WHERE id=".$id;
        $this->getEntityManager()->getConnection()->executeUpdate($sql);
        return $this;
    }

}
