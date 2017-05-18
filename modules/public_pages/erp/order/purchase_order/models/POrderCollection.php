<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POrderCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.7 $';
	
	public $field;

	function __construct($do = 'POrder', $tablename = 'po_headeroverview')
	{
		parent::__construct($do, $tablename);
	
	}

	public function getItems (&$sh, $cc = "")
	{
		
		if ($sh instanceof SearchHandler)
		{
			$DisplayFields = array('stitem_id'
								  ,'stitem'
								  ,'uom_name'
								  ,'on_order'
								  );
			
			$sh->setOrderby('stitem');
			
			$sh->setFields($DisplayFields);
			
			if (!empty($cc) && ($cc instanceof ConstraintChain))
			{
				$sh->addConstraintChain($cc);
			}
		}
		$this->_tablename = 'po_items';
		
	}
	
	public function getItemDates ($cc = "")
	{
		$sh = new SearchHandler($this,false);
		
		$DisplayFields = array('due_delivery_date'
							  ,'stitem_id'
							  ,'stitem'
							  ,'uom_name'
							  ,'on_order'
							  );
		
		$sh->setOrderby('due_delivery_date');
		
		$sh->setFields($DisplayFields);
		
		if (!empty($cc) && ($cc instanceof ConstraintChain))
		{
			$sh->addConstraintChain($cc);
		}
		
		$this->_tablename = 'po_itemdates';
		
		$this->load($sh);
		
		return $this;
		
	}

}

// End of POrderCollection
