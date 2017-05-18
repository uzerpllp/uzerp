<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintCodeCollection extends DataObjectCollection {

	protected $version = '$Revision: 1.5 $';
	
	function __construct($do = 'ComplaintCode')
	{
		parent::__construct($do);
	}

}

// End of ComplaintCodeCollection
