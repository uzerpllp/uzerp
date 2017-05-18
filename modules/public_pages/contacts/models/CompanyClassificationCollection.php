<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanyClassificationCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.5 $';
	
	function __construct($do = 'CompanyClassification')
	{
		parent::__construct($do);
	}

}

// End of CompanyClassificationCollection
