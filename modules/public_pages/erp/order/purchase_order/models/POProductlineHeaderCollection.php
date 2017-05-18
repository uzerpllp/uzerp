<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POProductlineHeaderCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.4 $';
	public $field;
	
	function __construct($do = 'POProductlineHeader', $tablename = 'po_productlines_header_overview')
	{
		
		parent::__construct($do, $tablename);
		
		$this->orderby=array('description', 'start_date');
		
	}
	
	function getItems(&$sh)
	{
		$this->_tablename = "po_productline_items";
		
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

// End of POProductlineHeaderCollection
