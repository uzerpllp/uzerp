<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POrderLineCollection extends DataObjectCollection {
	
	protected $version = '$Revision: 1.11 $';
	
	public $field;
		
	function __construct($do = 'POrderLine', $tablename = 'po_linesoverview')
	{
		parent::__construct($do, $tablename);
		
		$this->orderby=array('order_number', 'line_number');
	}

	public function getAuthSummary ($_order_id)
	{
		$sh=new SearchHandler($this, false);
		
		$fields=array("glcentre||' '||glaccount",'glcentre_id', 'glaccount_id');
		
		$sh->setGroupBy($fields);
		$sh->setOrderBy($fields);
		
		$fields[] = 'sum(base_net_value) as net_value';
		
		$sh->setFields($fields);
		
		$sh->addConstraint(new Constraint('order_id', '=', $_order_id));
		$sh->addConstraint(new Constraint('status', '!=', $this->_templateobject->cancelStatus()));
		
		$this->load($sh);
	}

}

// End of POrderLineCollection
