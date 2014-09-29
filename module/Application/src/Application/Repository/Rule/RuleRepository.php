<?php

namespace Application\Repository\Rule;

use Application\Model\Rule\ReferencableInterface;

class RuleRepository extends \Application\Repository\AbstractRepository
{

    const FORMULA_COMPONENT_PATTERN = '(Q\#|F\#|R\#|P\#|L\#|Y[+-]?|\d+|current|all|,)*';

    /**
     * Returns all items with permissions
     * @param string $action
     * @param string $search
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('rule');
        $this->addSearch($qb, $search, ['rule.name', 'rule.formula']);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the single character identifying the type of object in formula
     * @param \Application\Model\Rule\ReferencableInterface $reference
     * @return string single character to be used in formula
     * @throws \Exception
     */
    private function getObjectType(ReferencableInterface $reference)
    {
        if ($reference instanceof \Application\Model\Filter) {
            return 'F';
        } elseif ($reference instanceof \Application\Model\Questionnaire) {
            return 'Q';
        } elseif ($reference instanceof \Application\Model\Rule\Rule) {
            return 'R';
        } elseif ($reference instanceof \Application\Model\Part) {
            return 'P';
        } else {
            throw new \Exception('Unsupported reference type:' . get_class($reference));
        }
    }

    /**
     * Get all Rules with a reference to the given object
     * @param \Application\Model\Rule\ReferencableInterface $reference
     * @return \Application\Model\Rule\Rule[]
     */
    public function getAllReferencing(ReferencableInterface $reference)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata('Application\Model\Rule\Rule', 'rule');
        $native = $this->getEntityManager()->createNativeQuery('SELECT r.id, r.name, r.formula FROM rule AS r WHERE r.formula ~ :pattern', $rsm);

        $pattern = '{' . self::FORMULA_COMPONENT_PATTERN . $this->getObjectType($reference) . '\#' . $reference->getId() . '(?!\d)' . self::FORMULA_COMPONENT_PATTERN . '}';
        $native->setParameter('pattern', $pattern);

        return $native->getResult();
    }

}
