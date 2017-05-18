<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GLUnpostedTransactionCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.1 $';
	
	public $field;
	
	function __construct($do = 'GLUnpostedTransaction', $tablename = 'gl_unposted_transactions_overview')
	{
		parent::__construct($do, $tablename);
			
	}

}

// End of GLUnpostedTransactionCollection
