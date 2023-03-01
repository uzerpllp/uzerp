<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->paths([
        __DIR__ . '/lib',
        __DIR__ . '/modules',
        __DIR__ . '/plugins',
    ]);

    //define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81
    ]);

    $rectorConfig->skip([
        LongArrayToShortArrayRector::class,
    ]);

    $rectorConfig->bootstrapFiles([
        __DIR__ . '/utils/dev/loader.php',
    ]);
};
