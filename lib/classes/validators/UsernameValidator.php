<?php

/**
 *
 * Validate username field input
 *
 */
class UsernameValidator implements FieldValidation
{

    /**
     * Usernames must only contain lower-case letters and numbers
     *
     * @see FieldValidation::test()
     */
    function test(DataField $field, Array &$errors = array())
    {
        //Don't validate on get requests, we're possibly displaying and empty password field
        if (trim(strtolower($_SERVER['REQUEST_METHOD'])) === 'get') {
            return $field->value;
        }

        if (preg_match('/^[a-z0-9]+$/', $field->value)) {
            return $field->value;
        }

        $errors[$field->name] = 'Username must only contain lower-case letters and numbers';
        return false;
    }
}
?>
