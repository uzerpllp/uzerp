<?php

class STItemCodeValidator implements ModelValidation
{
    function test(DataObject $do, Array &$errors = array())
    {
        $field = $do->getField('item_code');
        if ($field->value != '') {
            if ($field->value !== str_replace(' ', '', $field->value)) {
                $errors[] = "Item code must not contain spaces";
                return false;
            }
        }
        return $do;
    }
}