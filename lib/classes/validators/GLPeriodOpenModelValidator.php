<?php

/**
 * Ensure GL period is open
 *
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2000-2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 * uzERP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 */
class GLPeriodOpenModelValidator implements ModelValidation
{

    function test(DataObject $do, Array &$errors = array())
    {
        // Guard against this validator being called for models that
        // do not have a glperiods_id field.
        if ($do->getField('glperiods_id')) {
            $period = DataObjectFactory::Factory('GLPeriod');
            $period = $period->load($do->glperiods_id);
            if ($period->closed === 't') {
                $errors[] = "Invalid period: {$period->year} - period {$period->period} is closed";
                return false;
            }
        }
        return $do;
    }
}
?>
