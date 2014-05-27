<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POAwaitingAuth extends DataObject {

	protected $version = '$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('username'=>'Person'
											,'order_limit');
	
	function __construct($tablename = 'po_awaiting_auth')
	{
		parent::__construct($tablename);
		
		$this->idField = 'id';
		
 		$this->validateUniquenessOf(array('order_id', 'username'));
 		
 		$this->belongsTo('POrder', 'order_id', 'order');
		$this->belongsTo('User', 'username', 'person');
  		
	}
	
}

// End of POAwaitingAuth
