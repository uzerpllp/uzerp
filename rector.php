<?php
declare (strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()

    ->withPaths([
        __DIR__ . '/lib',
        __DIR__ . '/modules',
        __DIR__ . '/plugins',
    ])


    ->withIndent(indentChar: '	', indentSize: 1)

    //define sets of rules
    ->withPhpSets()

    ->withPhpVersion(PhpVersion::PHP_83)

    ->withSets([
	LevelSetList::UP_TO_PHP_83
    ])

//    ->withRules([
//	Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector::class,
//	Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector::class,
//	Rector\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector::class
//    ])

    ->withSkip([
	Rector\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector::class,
	// this is fixed in code formatting before applying rector
        Rector\Php54\Rector\Array_\LongArrayToShortArrayRector::class,
	// drop this for php 7.4+
	Rector\Php80\Rector\FuncCall\ClassOnObjectRector::class,
	// drop this for PHP 7.4
	Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector::class,
    ])

    ->withBootstrapFiles([
        __DIR__ . '/utils/dev/loader.php',
    ]);
