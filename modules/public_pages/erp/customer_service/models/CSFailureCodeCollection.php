<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CSFailureCodeCollection extends DataObjectCollection
{
	
	protected $version='$Revision: 1.5 $';
	
	public $field;
		
	function __construct($do='CSFailureCode')
	{
		
		parent::__construct($do);
					
	}	

}

// End of CSFailureCodeCollection
