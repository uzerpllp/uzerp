<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PersonNote extends DataObject
{

	protected $version = '$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('title','note','created');

	function __construct($tablename = 'person_notes')
	{
		parent::__construct($tablename);
		
		$this->belongsTo('Person','person_id','person');
	}

}

// End of PersonNote
