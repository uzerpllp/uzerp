<?php
declare (strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()

    ->withPaths([
        __DIR__ . '/lib',
        __DIR__ . '/lib/classes/standard',
        __DIR__ . '/modules',
        __DIR__ . '/plugins',
    ])

    //define sets of rules
    //->withPhpSets()

    ->withPhpVersion(PhpVersion::PHP_83)

    //->withSets([
//	LevelSetList::UP_TO_PHP_83
  //  ])

    ->withRules([
	Rector\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector::class
    ])

    ->withSkip([
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
