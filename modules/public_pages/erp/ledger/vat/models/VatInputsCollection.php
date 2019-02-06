<?php
/**
 *	@author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 *	@license GPLv3 or later
 *	@copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class VatInputsCollection extends DataObjectCollection {
    
    public $field;
    
    function __construct($do='VatInputs', $tablename='gltransactions_vat_inputs') {
        parent::__construct($do, $tablename);
    }
}
?>