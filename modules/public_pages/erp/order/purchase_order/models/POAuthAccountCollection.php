<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POAuthAccountCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.5 $';
	
	public $field;

	function __construct($do = 'POAuthAccount')
	{
		parent::__construct($do);
	
		$this->view = '';
		
	}
		
}

// End of POAuthAccountCollection
