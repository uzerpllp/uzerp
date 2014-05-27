<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class STProductgroup extends DataObject {

	protected $version='$Revision: 1.8 $';
	
	function __construct($tablename='st_productgroups') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->orderby=array('product_group', 'description');
		
		$this->identifierField=array('product_group', 'description');
		$this->hasMany('SLDiscount', 'discounts', 'prod_group_id');
		$this->validateUniquenessOf(array('product_group', 'description')); 
		
	}

}
?>