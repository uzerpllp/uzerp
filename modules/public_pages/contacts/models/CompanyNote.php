<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanyNote extends DataObject
{
	
	protected $version = '$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('title'			=> 'Title'
										   ,'note'			=> 'Note'
										   ,'company'		=> 'Company'
										   ,'owner'			=> 'Owner'
										   ,'alteredby'		=> 'Altered By'
										   ,'lastupdated'	=> 'Updated'
										   );
	
	function __construct($tablename = 'company_notes')
	{
		parent::__construct($tablename);
		
		$this->belongsTo('Company','company_id','company');
	}
}	

// End of CompanyNote
