<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POAuthAccount extends DataObject
{

	protected $version = '$Revision: 1.5 $';
	
	function __construct($tablename = 'po_auth_accounts')
	{
		parent::__construct($tablename);
		
		$this->idField	= 'id';
		$this->view		= '';
		
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
  		$this->belongsTo('POAuthLimit', 'po_auth_limit_id', 'po_auth_limit');
 	
	}

}

// End of POAuthAccount
