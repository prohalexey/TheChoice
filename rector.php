<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\TypedCollections\Rector\Assign\ArrayDimFetchAssignToAddCollectionCallRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayFindRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\RemoveDataProviderParamKeysRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\Symfony\CodeQuality\Rector\Class_\InlineClassRoutePrefixRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ActionSuffixRemoverRector;
use Rector\Symfony\Symfony73\Rector\Class_\InvokableCommandInputAttributeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeForArrayMapRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/test',
    ])
    ->withPreparedSets(
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    )
    ->withCache(__DIR__ . '/var/rector')
    ->withParallel(240)
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withSkip([
        ActionSuffixRemoverRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        ChangeSwitchToMatchRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        CombineIfRector::class,
        CompactToVariablesRector::class,
        DisallowedEmptyRuleFixerRector::class,
        EncapsedStringsToSprintfRector::class,
        ExplicitBoolCompareRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        ForeachToArrayAnyRector::class,
        ForeachToArrayFindRector::class,
        InlineClassRoutePrefixRector::class,
        InlineConstructorDefaultToPropertyRector::class,
        InvokableCommandInputAttributeRector::class,
        LocallyCalledStaticMethodToNonStaticRector::class,
        MakeInheritedMethodVisibilitySameAsParentRector::class,
        NullToStrictStringFuncCallArgRector::class,
        PreferPHPUnitThisCallRector::class,
        RemoveDataProviderParamKeysRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class,
        SimplifyIfElseToTernaryRector::class,
        ThrowWithPreviousExceptionRector::class,
        YieldDataProviderRector::class,
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
    ])
    ->withRules([
        ArrayDimFetchAssignToAddCollectionCallRector::class,
        AddClosureParamTypeForArrayMapRector::class,
        AddLiteralSeparatorToNumberRector::class,
    ])
;
