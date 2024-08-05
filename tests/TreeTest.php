<?php

require('modules/public_pages/erp/manufacturing/controllers/Tree.php');

test('Test Tree Class', function () {
    // Remove prev error handler
    restore_error_handler();

    $structure = array(
        'Item A (top)' => array(
            'Item B (depth: 1)' => array(
                'Item C (depth: 2)' => array(),
                'Item D (depth: 2)' => array(
                    'Item E (depth: 3)' => array())
            ),
            'Item X (depth: 1)' => array(
                'Item Y (depth: 2)' => array(
                    'Item Z (depth: 3)' => array())
            )
        )
    );

    $tree_children_first = new \Tree($structure, \Tree::CHILDREN_FIRST);
    $count = 0;
    foreach ($tree_children_first as $key => $value) {
        if ($count == 0) expect($key)->toBe('Item C (depth: 2)');
        break;
    }
    $count = 0;
    $tree_parents_first = new \Tree($structure, \Tree::PARENTS_FIRST);
    foreach ($tree_parents_first as $key => $value) {
        if ($count == 0) expect($key)->toBe('Item A (top)');
        break;
    }
});