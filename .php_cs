<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
        ->exclude('data')
        ->exclude('vendor')
        ->exclude('node_modules')
        ->exclude('nbproject')
        ->exclude('htdocs/lib')
        ->in(__DIR__)
;

return Symfony\CS\Config\Config::create()
                ->fixers(array(
                    'encoding',
                    'linefeed',
                    'trailing_spaces',
                    'php_closing_tag',
//                    'unused_use', // This breaks usage of traits in parent class, specifically in Application\Service\Calculator\Calculator, see https://github.com/fabpot/PHP-CS-Fixer/issues/217
                    'include',
                    'visibility',
                    'indentation',
                    'lowercase_constants',
                    'braces',
                    'standardize_not_equal',
                    'lowercase_keywords',
                    'extra_empty_lines',
                    'object_operator',
                    'return',
//                    'function_declaration', // This does not recognize javascript function in PHP string, and mis-format PHP closures
                    'short_tag',
                    'new_with_braces',
                    'spaces_cast',
//                    'phpdoc_params', // Waste of time
                    'psr0',
                    'controls_spaces',
                    'elseif',
                    'eof_ending',
                ))
                ->finder($finder)
;
