<?php

namespace Application\Repository;

class QuestionRepository extends AbstractRepository
{

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($parentName, \Application\Model\AbstractModel $parent = null)
    {
        $permissionDql = $this->getPermissionDql('survey', 'Question-read');
        $query = $this->getEntityManager()->createQuery("SELECT question
            FROM Application\Model\Question\AbstractQuestion question
            JOIN question.survey survey
            $permissionDql
            WHERE
            $parentName = :parent
            "
        );

        $query->setParameters(array(
            'parent' => $parent
        ));

        return $query->getResult();
    }

    public function changeType($id, $type)
    {
        $type = strtolower(str_replace("Application\\Model\\Question\\", "", $type));
        $sql = "UPDATE question set dtype='" . $type . "' WHERE id=" . $id;
        $this->getEntityManager()->getConnection()->executeUpdate($sql);

        return $this;
    }

    /**
     * Get one question, without taking into consideration its type
     */
    public function getOneById($id)
    {

        $query = $this->getEntityManager()->createQuery("SELECT q FROM Application\Model\Question\AbstractQuestion q WHERE q.id = :id");

        $params = array(
            'id' => $id,
        );

        $query->setParameters($params);
        $question = $query->getOneOrNullResult();

        return $question;
    }

}
