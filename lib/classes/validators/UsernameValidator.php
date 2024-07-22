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
    function test(DataField $field, Array &$errors=array())
    {
        // Don't validate on get requests, we're possibly displaying and empty password field
        if (trim(strtolower((string) $_SERVER['REQUEST_METHOD'])) === 'get') {
            return $field->value;
        }

        // Don't validate existing user names (hidden form field), it might be mixed case
        // on older installs and that is ok.
        //
        // NOTE: Adding a new user uses the same form as updating a user. If the username
        // entered matches an existing username, the existing User object is updated.
        if (trim(strtolower((string) $_SERVER['REQUEST_METHOD'])) === 'post') {
            $user = new User();
            $user->load($field->value);
            if ($user->isLoaded()) {
                return $field->value;
            }
        }

        if (preg_match('/^[a-z0-9]+$/', $field->value)) {
            return $field->value;
        }

        $errors[$field->name] = 'Username must only contain lower-case letters and numbers';
        return false;
    }
}
?>
