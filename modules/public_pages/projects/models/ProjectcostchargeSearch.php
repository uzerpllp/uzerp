<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectcostchargeSearch extends BaseSearch {

	protected $version='$Revision: 1.1 $';
	
	protected $fields=array();
		
	public static function purchaseOrders($search_data=null, &$errors, $defaults=null) {
		$search = new ProjectcostchargeSearch($defaults);
		
		$search->addSearchField(
			'project_id',
			'project_id',
			'hidden',
			'',
			'hidden',
			false
		);

		$search->addSearchField(
			'plmaster_id',
			'Supplier',
			'select'
		);

		$account = new PLSupplier();
		$accounts = $account->getAll();
		$options = array('' => 'All');
		$options += $accounts;
		$search->setOptions('plmaster_id',$options);

		$search->addSearchField(
			'order_id',
			'Orders',
			'select'
		);

		$unassigned_list=new POrderLine();
		$unassigned_list->idField='order_id';
		$unassigned_list->identifierField='order_number';
		$cc=new ConstraintChain();
		$subquery="select item_id from project_costs_charges where item_type='PO'";
		$cc->add(new Constraint('id', 'not in', '('.$subquery.')'));
		
		$search->setOptions('order_id',$unassigned_list->getAll($cc, false, true));
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
	public static function salesInvoices($search_data=null, &$errors, $defaults=null) {
		$search = new ProjectcostchargeSearch($defaults);
		
		$search->addSearchField(
			'project_id',
			'project_id',
			'hidden',
			'',
			'hidden',
			false
		);

		$search->addSearchField(
			'slmaster_id',
			'Customer',
			'hidden',
			'',
			'hidden'
		);

		$search->addSearchField(
			'invoice_id',
			'Invoices',
			'select'
		);

		$unassigned_list=new SInvoiceLine();
		$unassigned_list->idField='invoice_id';
		$unassigned_list->identifierField='invoice_number';
		$cc=new ConstraintChain();
		$subquery="select item_id from project_costs_charges where item_type='SI'";
		$cc->add(new Constraint('id', 'not in', '('.$subquery.')'));
		if (isset($search_data['slmaster_id']))
		{
			$slmaster_id=$search_data['slmaster_id'];
		}
		elseif (isset($defaults['slmaster_id']))
		{
			$slmaster_id=$defaults['slmaster_id'];
		}
		$cc->add(new Constraint('slmaster_id', '=', $slmaster_id));
		
		$search->setOptions('invoice_id',$unassigned_list->getAll($cc, false, true));
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>