<?php

namespace Application\Repository;

class QuestionRepository extends AbstractChildRepository
{

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($action = 'read', $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('question')
                ->join('question.survey', 'survey', \Doctrine\ORM\Query\Expr\Join::WITH)
                ->where('question.' . $parentName . ' = :parent')
                ->setParameter('parent', $parent)
                ->orderBy('question.sorting')
        ;

        $this->addPermission($qb, 'survey', \Application\Model\Permission::getPermissionName($this, $action));

        return $qb->getQuery()->getResult();
    }

    public function changeType($id, \Application\Model\QuestionType $questionType)
    {
        $class = \Application\Model\QuestionType::getClass($questionType);
        $dtype = strtolower(str_replace("Application\\Model\\Question\\", '', $class));

        $sql = "UPDATE question SET dtype='" . $dtype . "' WHERE id=" . $id;
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
