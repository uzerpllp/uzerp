<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SLAnalysis extends DataObject
{

	protected $version = '$Revision: 1.6 $';
	
	protected $defaultDisplayFields = array('id', 'name');
											
	function __construct($tablename = 'sl_analysis')
	{
		parent::__construct($tablename);

	}
	
}

// End of SLAnalysis
