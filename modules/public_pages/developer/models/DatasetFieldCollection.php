<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DatasetFieldCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.1 $';
	
	function __construct($do = 'DatasetField')
	{
		
		parent::__construct($do);
		
		$this->orderby = 'position';
	}

}

// End of DatasetFieldCollection
