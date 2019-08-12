<?php
 
/**
 * Validate an EAN number
 *
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 * uzERP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 */
class EanModelValidator implements ModelValidation
{

    function test(DataObject $do, Array &$errors = array())
    {
        $field = $do->getField('ean');
        if ($field->value != '') {
            $ean = new BarcodeValidator($field->value);
            if (!$ean->isValid() || $ean->getType() !== $ean::TYPE_EAN) {
                $errors[] = "EAN must be a valid EAN-13 code number";
                return false;
            }
        }
        return $do;
    }
}