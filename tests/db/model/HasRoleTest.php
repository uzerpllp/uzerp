<?php

test('Test model methods', function () {
    $model = new HasRole;
    $roles = $model->getRoleID(EGS_USERNAME);
    $roles_empty = $model->getRoleID(null);
    $users_empty = $model->getUsers(null);

    expect($roles_empty)->toBeArray();
    expect($roles_empty)->toBeEmpty();
    expect($users_empty)->toBeArray();
    expect($users_empty)->toBeEmpty();

    foreach ($roles as $id => $role_id) {
        $users = $model->getUsers($role_id);
        expect($users)->toBeArray();
        expect($users)->not->toBeEmpty();
    }
});
