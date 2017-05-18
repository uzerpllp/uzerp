<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class PasswordValidator implements FieldValidation
{

    function test(DataField $field, Array &$errors = array())
    {
        if (strlen($field->value) >= 10) {
            return password_hash($field->value, PASSWORD_DEFAULT);
        }

        $errors[$field->name] = 'Password must be at least 10 characters long';
        return false;
    }
}
?>
