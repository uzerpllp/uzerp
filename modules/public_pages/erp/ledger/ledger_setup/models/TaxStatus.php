<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class TaxStatus extends DataObject
{

	protected $version='$Revision: 1.6 $';
	
	function __construct($tablename='tax_statuses')
	{
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField = 'description';
		 
		$this->validateUniquenessOf('description');
		
	}

}

// End of TaxStatus
