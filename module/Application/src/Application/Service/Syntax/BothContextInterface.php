<?php

namespace Application\Service\Syntax;

/**
 * This interface is used to identify syntaxes that are valid in both contexts.
 * Both classes (BeforeRegression and AfterRegression) must implement the interface.
 * It does NOT mean that both tokens have the same meaning in the two contexts,
 * but only that they share the same syntax.
 */
interface BothContextInterface
{

}
