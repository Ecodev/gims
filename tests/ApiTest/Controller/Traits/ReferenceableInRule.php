<?php

namespace ApiTest\Controller\Traits;

trait ReferenceableInRule
{

    protected function subtestMemberCanDelete()
    {
        // Reference objects in our formula
        $formula = sprintf('={F#%1$s,Q#%2$s,P#%3$s} + {R#%4$s,Q#%2$s,P#%3$s}', $this->filter->getId(), $this->questionnaire->getId(), $this->part->getId(), $this->rule->getId());
        $rule = new \Application\Model\Rule\Rule('tst rule that should be deleted');
        $rule->setFormula($formula);
        $this->getEntityManager()->persist($rule);
        $this->getEntityManager()->flush();

        // delete the referenced object will implicitly delete our Rule
        parent::subtestMemberCanDelete();

        $reloadedRule = $this->getEntityManager()->getRepository('Application\Model\Rule\Rule')->findOneById($rule->getId());
        $this->assertTrue(is_null($reloadedRule), 'Rule should be deleted by DB trigger');
    }

}
