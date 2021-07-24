<?php
/**
 *	@author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 *	@license GPLv3 or later
 *	@copyright (c) 2021 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class VatAdjustmentCollection extends DataObjectCollection {
    
    public $field;
    
    function __construct($do='VatAdjustment', $tablename='vat_adjustment_overview') {
        parent::__construct($do, $tablename);
    }
}
?>