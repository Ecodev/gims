<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
        ->exclude('data')
        ->exclude('vendor')
        ->in(__DIR__)
;

return Symfony\CS\Config\Config::create()
                ->fixers(array(
                    'indentation',
                    'elseif',
                    'linefeed',
                    'trailing_spaces',
//                    'unused_use', // This breaks usage of traits in parent class, specifically in Application\Service\Calculator\Calculator
                    'visibility',
                    'return',
                    'short_tag',
//                    'braces', // still not quite sure about this one... it makes it very hard to comment if/else structure
//                    'include', // This breaks usage of include within function call
                    'php_closing_tag',
                    'extra_empty_lines',
                    'psr0',
                    'controls_spaces',
                    'elseif',
                    'eof_ending',
                ))
                ->finder($finder)
;
