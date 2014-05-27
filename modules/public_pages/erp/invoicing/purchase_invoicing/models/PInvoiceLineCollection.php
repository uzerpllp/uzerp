<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class PInvoiceLineCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.6 $';
	
	public $field;
		
	function __construct($do='PInvoiceLine', $tablename='pi_linesoverview') {
		parent::__construct($do, $tablename);
		$this->orderby='line_number';
	}
	
	static function getInvoicedQty ($order_line_id) {
// Gets the total qty invoiced for a PO Line
		$pilines=new PInvoiceLineCollection(new PInvoiceLine);
		$sh=new SearchHandler($pilines, false);
		$sh->addConstraint(new Constraint('order_line_id', '=', $order_line_id));
		$pilines->load($sh);
		$qty=0;
		foreach ($pilines as $line) {
			$qty+=$line->purchase_qty;
		}
		return $qty;
	}
		
}
?>