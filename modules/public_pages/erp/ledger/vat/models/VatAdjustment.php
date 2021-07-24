<?php
/**
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2021 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 * uzERP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 */
class VatAdjustment extends DataObject
{
    protected $defaultDisplayFields = [
        'year',
        'tax_period',
        'reference',
        'comment',
        'vat_due_sales', //Box 1 - VAT Due On Sales
        'vat_reclaimed_curr_period', //Box 4 - Input Tax - CHECK THIS!!
        'total_value_sales_ex_vat', //Box 6 - Sales Exc. VAT
        'total_value_purchase_ex_vat' //Box 7 - Purchases Exc. VAT
    ];


    public function __construct($tablename='vat_adjustment') {
        parent::__construct($tablename);

        // Set specific characteristics
        $this->idField='id';
        $this->orderby = ['vat_return_id', 'reference'];
        
        // Define relationships
        $this->belongsTo('VATReturn', 'vat_return_id', 'year');
        $this->belongsTo('VATReturn', 'vat_return_id', 'tax_period');

        // Define system defaults
        $this->getField('vat_due_sales')->setDefault('0.00');
        $this->getField('vat_reclaimed_curr_period')->setDefault('0.00');
        $this->getField('total_value_sales_ex_vat')->setDefault('0.00');
        $this->getField('total_value_purchase_ex_vat')->setDefault('0.00');

        // Define field formats		
		$this->getField('vat_due_sales')->setFormatter(new NumericFormatter());
        $this->getField('vat_reclaimed_curr_period')->setFormatter(new NumericFormatter());
        $this->getField('total_value_sales_ex_vat')->setFormatter(new NumericFormatter());
        $this->getField('total_value_purchase_ex_vat')->setFormatter(new NumericFormatter());
    }
}
?>