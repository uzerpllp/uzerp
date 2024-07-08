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

    ->withSets([
	LevelSetList::UP_TO_PHP_83
    ])


    ->withSkip([
        Rector\Php54\Rector\Array_\LongArrayToShortArrayRector::class,
    ])

    ->withBootstrapFiles([
        __DIR__ . '/utils/dev/loader.php',
    ]);
