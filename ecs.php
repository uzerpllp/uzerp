<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/conf',
        __DIR__ . '/lib',
        __DIR__ . '/modules',
        __DIR__ . '/plugins',
        __DIR__ . '/schema/phinx',
    ]);

    // this way you add a single rule
    $ecsConfig->rules([
        NoUnusedImportsFixer::class,
	PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer::class
    ]);

    // this way you can add sets - group of rules
    $ecsConfig->sets([
        // run and fix, one by one
         SetList::SPACES,
         SetList::ARRAY,
         SetList::DOCBLOCK,
         SetList::NAMESPACES,
         SetList::COMMENTS,
         SetList::PSR_12,
    ]);
};
