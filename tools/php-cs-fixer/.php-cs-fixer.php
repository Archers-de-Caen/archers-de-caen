<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PHP81Migration' => true,
        '@PHP80Migration:risky' => true,
    ])
    ->setFinder($finder)
;
