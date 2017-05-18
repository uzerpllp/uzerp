<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GLTransactionHeaderCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.1 $';
	
	function __construct($do = 'GLTransactionHeader', $tablename = 'gl_transactions_header_overview')
	{
		parent::__construct($do, $tablename);
		
		$this->orderby	= 'created';
		$this->direction	= 'desc';
		
	}

}

// End of GLTransactionHeaderCollection
