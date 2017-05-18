<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HourPaymentTypeCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.2 $';
	
	public function __construct($do = 'HourPaymentType', $tablename = 'hours_payment_types_overview')
	{
		parent::__construct($do, $tablename);
	}

}

// End of HourPaymentTypeCollection
