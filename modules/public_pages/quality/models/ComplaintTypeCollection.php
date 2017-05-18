<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintTypeCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.5 $';
	
	function __construct($do = 'ComplaintType')
	{
		parent::__construct($do);
	}

}

// End of ComplaintTypeCollection
