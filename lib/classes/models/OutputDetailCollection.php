<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class OutputDetailCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.4 $';

	function __construct($do = 'OutputDetail', $tablename = 'output_details')
	{
		parent::__construct($do, $tablename);

	}

}

// End of OutputDetailCollection
