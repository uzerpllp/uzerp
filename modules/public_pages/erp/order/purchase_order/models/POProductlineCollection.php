<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POProductlineCollection extends DataObjectCollection
{
	
	protected $version='$Revision: 1.10 $';
	
	public $field;
	
	function __construct($do='POProductline', $tablename='po_productlines_overview')
	{
		parent::__construct($do, $tablename);
		
	}
	
	function getItems(&$sh)
	{
		$this->_tablename="po_productline_items";
		
		if ($sh instanceof SearchHandler)
		{
			$sh->setFields(array('id'
								,'stitem_id'
								,'stuom_id'
								,'uom_name'
								,'stitem'));
			
			$sh->setOrderby('stitem');
		}
	}

}

// End of POProductlineCollection
