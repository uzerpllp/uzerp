<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HRAuthoriserCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.1 $';
	
	public function __construct($do = 'HRAuthoriser', $tablename = 'hr_authorisers_overview')
	{
		parent::__construct($do, $tablename);
	}

}

// End of HRAuthoriserCollection
