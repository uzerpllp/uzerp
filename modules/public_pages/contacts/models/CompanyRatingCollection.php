<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanyRatingCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.5 $';
	
	function __construct($do = 'CompanyRating')
	{
		parent::__construct($do);
	}

}

// End of CompanyRatingCollection
