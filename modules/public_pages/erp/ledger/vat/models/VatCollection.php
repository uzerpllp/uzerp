<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class VatCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.13 $';
	public $field;
	
	function __construct($do='Vat') {
		parent::__construct($do);
			
	}
	
	function eu_arrivals($sh) {
		$this->setTablename('tax_eu_arrivals');
		$this->title='VAT EU Arrivals';
		$fields=array('id'
					, 'delivery_terms'
					, 'received_date'
					, 'received_qty'
					, 'net_mass'
					, 'uom_name'
					, 'item_description'
					, 'delivery_note'
					, 'invoice_number'
					, 'supplier'
					, 'order_number');
		$sh->setorderby(array('delivery_terms'
							,'received_date'
							,'supplier'
							,'item_description'));
		$sh->setFields($fields);
	}

	function eu_despatches($sh) {
		$this->setTablename('tax_eu_despatches');
		$this->title='VAT EU Despatches';
		$fields=array('id'
					, 'delivery_terms'
					, 'despatch_date'
					, 'despatch_qty'
					, 'net_mass'
					, 'uom_name'
					, 'item_description'
					, 'invoice_number'
					, 'customer'
					, 'order_number');
		$sh->setorderby(array('delivery_terms'
							,'despatch_date'
							,'customer'
							,'item_description'));
		$sh->setFields($fields);
	}

	function eu_saleslist($sh) {
		$this->setTablename('tax_eu_saleslist');
		$sh->setorderby(array('customer'
							,'invoice_date'
							,'invoice_number'));
		$this->title='VAT EU Saleslist';
		$fields=array('id'
					, 'invoice_date'
					, 'despatch_date'
					, 'delivery_note'
					, 'transaction_type'
					, 'invoice_number'
					, 'customer'
					, 'vat_number'
					, 'country'
					, 'sales_order_number'
					, 'base_tax_value'
					, 'base_net_value');
		$sh->setFields($fields);
	}

}
?>