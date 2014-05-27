<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectNoteType extends DataObject {
	
	protected $version = '$Revision: 1.1 $';
	
	protected $defaultDisplayFields = array(
		'name'
	);
	
	function __construct($tablename = 'project_note_types')
	{
		
		parent::__construct($tablename);
		
		$this->idField			= 'id';
		$this->identifierField	= 'name';

	}

}

// end of ProjectNoteType.php