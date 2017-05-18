<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class STTypecodeCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.5 $';
	
	public $field;
		
	function __construct($do = 'STTypecode')
	{
		parent::__construct($do, 'st_typecodes_overview');
					
	}

}

// End of STTypecodeCollection
