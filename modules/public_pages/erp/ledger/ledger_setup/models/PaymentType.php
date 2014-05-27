<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class PaymentType extends DataObject {

	protected $version='$Revision: 1.6 $';
	
	protected $defaultDisplayFields = array('name'
											,'method'
											);
	
	function __construct($tablename='sypaytypes') {
		parent::__construct($tablename);
		$this->idField='id';
		$cc=new ConstraintChain();
		$cc->add(new Constraint('category', '=', 'PT'));
 		$this->belongsTo('InjectorClass', 'method_id', 'method', $cc);
 		$this->hasOne('InjectorClass', 'method_id', 'payment_class');
	}

}
?>