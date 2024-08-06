<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ARAnalysisCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.6 $';

	public $field;

	function __construct($do = 'ARAnalysis')
	{

		parent::__construct($do);

	}

}

// End of ARAnalysisCollection
