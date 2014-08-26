<?php

namespace ApplicationTest\Validator;

use \Application\Validator\Rule;

/**
 * @group Service
 */
class RuleTest extends \ApplicationTest\Controller\AbstractController
{

    public function testValidation()
    {
        $validator = new Rule();
        $rule = new \Application\Model\Rule\Rule('tst rule');

        $this->assertTrue($validator->isValid($rule), 'New Rule is valid by default');

        $rule->setFormula('');
        $this->assertFalse($validator->isValid($rule), 'Empty formula is invalid');
        $this->assertArrayHasKey(Rule::START_EQUAL, $validator->getMessages());

        $rule->setFormula('=1 +
            2');
        $this->assertTrue($validator->isValid($rule), 'Multiline formula is valid');

        $rule->setFormula('=2{self}');
        $this->assertFalse($validator->isValid($rule), 'GIMS syntax sticked to numbers without any operator is invalid');
        $this->assertArrayHasKey(Rule::INVALID_SYNTAX, $validator->getMessages());

        $rule->setFormula('=SUM({F#12,Q#34,P#56}) + IF(ISTEXT({F#12,Q#34}), {Q#34,P#56}, {self})');
        $this->assertTrue($validator->isValid($rule), 'Correct use of Excel and GIMS syntax is ok');

        $rule->setFormula('=SUM(');
        $this->assertFalse($validator->isValid($rule), 'Invalid Excel formula is invalid');
        $this->assertArrayHasKey(Rule::INVALID_SYNTAX, $validator->getMessages());

        $rule->setFormula('={F#1,}');
        $this->assertFalse($validator->isValid($rule), 'Invalid GIMS formula is invalid');
        $this->assertArrayHasKey(Rule::INVALID_SYNTAX, $validator->getMessages());

        $rule->setFormula('={F#12,Q#34,P#56} + {Y}');
        $this->assertFalse($validator->isValid($rule), 'Cannot used both kind of tokens');
        $this->assertArrayHasKey(Rule::MIXED_TOKENS, $validator->getMessages());

        $rule->setFormula('={F#12,Q#34,P#56} + {self}');
        $this->assertTrue($validator->isValid($rule), 'Self syntax can be used with before regression tokens');

        $rule->setFormula('={self} + {Y}');
        $this->assertTrue($validator->isValid($rule), 'Self syntax can be used with after regression tokens');

        $rule1 = new \Application\Model\Rule\Rule('tst rule');
        $usage1 = new \Application\Model\Rule\FilterGeonameUsage();
        $usage1->setRule($rule1);
        $rule1->setFormula('={F#12,Q#34,P#56}');
        $this->assertFalse($validator->isValid($rule1), 'Non-regression token cannot be used if rule is used in regression context');
        $this->assertArrayHasKey(Rule::BEFORE_WITH_AFTER_REGRESSION, $validator->getMessages());

        $rule2 = new \Application\Model\Rule\Rule('tst rule');
        $usage2 = new \Application\Model\Rule\FilterQuestionnaireUsage();
        $usage2->setRule($rule2);
        $rule2->setFormula('={Y}');
        $this->assertFalse($validator->isValid($rule2), 'Regression token cannot be used if Rule is used in non-regression context');
        $this->assertArrayHasKey(Rule::AFTER_WITH_BEFORE_REGRESSION, $validator->getMessages());

        $rule3 = new \Application\Model\Rule\Rule('tst rule');
        $usage3 = new \Application\Model\Rule\QuestionnaireUsage();
        $usage3->setRule($rule3);
        $rule3->setFormula('={Y}');
        $this->assertFalse($validator->isValid($rule3), 'Regression token cannot be used if Rule is used in non-regression context');
        $this->assertArrayHasKey(Rule::AFTER_WITH_BEFORE_REGRESSION, $validator->getMessages());
    }

}
