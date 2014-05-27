<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeePaymentTypeCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.1 $';
	
	public function __construct($do = 'EmployeePaymentType')
	{
		parent::__construct($do);
	}

}

// End of EmployeePaymentTypeCollection
