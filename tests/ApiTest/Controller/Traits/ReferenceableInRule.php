<?php

namespace ApiTest\Controller\Traits;

use Application\Model\Geoname;
use Application\Model\Rule\QuestionnaireUsage;
use Application\Model\Rule\Rule;
use Zend\Http\Request;

trait ReferenceableInRule
{

    /**
     * Create and flush a new rule with references to existing objects
     * @return \Application\Model\Rule\Rule
     */
    private function createAnotherRule()
    {
        $formula = sprintf('={F#%1$s,Q#%2$s,P#%3$s} + {R#%4$s,Q#%2$s,P#%3$s}', $this->filter->getId(), $this->questionnaire->getId(), $this->part->getId(), $this->rule->getId());

        $rule = new Rule('tst rule that should be deleted');
        $rule->setFormula($formula);
        $this->getEntityManager()->persist($rule);
        $this->getEntityManager()->flush();

        return $rule;
    }

    protected function subtestMemberCanDelete()
    {
        $rule = $this->createAnotherRule();

        // delete the referenced object will implicitly delete our Rule
        parent::subtestMemberCanDelete();

        $reloadedRule = $this->getEntityManager()->getRepository('Application\Model\Rule\Rule')->findOneById($rule->getId());
        $this->assertTrue(is_null($reloadedRule), 'Rule should be deleted by DB trigger');
    }

    public function testMemberCannotDeleteWhenObjectIsReferencedInARuleThatIsUsedSomewhereElse()
    {
        $rule = $this->createAnotherRule();
        $questionnaire2 = $this->createAnotherQuestionnaire();

        // Move the questionnaire to another geoname, so we are certain that the
        // questionnaire is not taken into consideration because of same geoname,
        // but really because the Rule has a reference to it.
        // This is necessary only when deleting a Rule (but doesn't hurst for other cases).
        $geoname2 = new Geoname('test geoname 2');
        $questionnaire2->setGeoname($geoname2);

        $usage = new QuestionnaireUsage();
        $usage->setRule($rule)->setPart($this->part)->setQuestionnaire($questionnaire2)->setJustification('prevent deletion of referenced object because of this usage');

        $this->getEntityManager()->persist($usage);
        $this->getEntityManager()->persist($geoname2);
        $this->getEntityManager()->flush();

        $route = $this->getRoute('delete');
        $this->dispatch($route, Request::METHOD_DELETE);
        $this->assertResponseStatusCode(403);
    }
}
