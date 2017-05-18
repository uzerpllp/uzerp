<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ReportTypeCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.1 $';
	
	function __construct($do = 'ReportType')
	{
		parent::__construct($do);
	}

}

// End of ReportTypeCollection
