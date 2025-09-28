<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Symfony73\Rector\Class_\GetFiltersToAsTwigFilterAttributeRector;
use Rector\Symfony\Symfony73\Rector\Class_\GetFunctionsToAsTwigFunctionAttributeRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return RectorConfig::configure()
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::PHP_83,
        SetList::PRIVATIZATION,
        SetList::INSTANCEOF,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::DEAD_CODE,
    ])
    ->withSkip([
        ExplicitBoolCompareRector::class,
        RemoveUnusedPublicMethodParameterRector::class,
    ])
    ->withSkip([
        GetFiltersToAsTwigFilterAttributeRector::class,
        GetFunctionsToAsTwigFunctionAttributeRector::class
    ])
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    );
