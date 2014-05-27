<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ComplaintCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.7 $';
	
	function __construct($do = 'Complaint', $tablename = 'qc_complaints_overview')
	{
		parent::__construct($do, $tablename);
		
	}

}

// End of ComplaintCollection
