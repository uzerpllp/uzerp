<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Projectworktype extends DataObject
{
	
	protected $version='$Revision: 1.5 $';
	
	function __construct($tablename='project_work_types')
	{
		parent::__construct($tablename);
		
		$this->idField='id';
		
		$this->identifierField='title';
		
		$this->belongsTo('Projectworktype', 'parent_id', 'parent');
		
		$this->actsAsTree('parent_id'); 
		
	}
	
}

// End of Projectworktype
