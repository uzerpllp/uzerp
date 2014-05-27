<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class OutputDetail extends DataObject
{

	protected $version = '$Revision: 1.4 $';
	
	function __construct($tablename = 'output_details')
	{
		parent::__construct($tablename);

		$this->idField	= 'id';

		$this->hasOne('OutputHeader', 'output_header_id', 'output_header');
		
	}

}

// End of OutputDetail
