<?php

namespace Application\ORM\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Allow us to use CAST() functions in DQL
 */
class CastFunction extends FunctionNode
{

    private $propertyName = null;
    private $type = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->propertyName = $parser->StringPrimary();

        $parser->match(Lexer::T_AS);

        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $this->type = $lexer->token['value'];

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('CONVERT(%s, %s)', $this->propertyName->dispatch($sqlWalker), $this->type);
    }
}
