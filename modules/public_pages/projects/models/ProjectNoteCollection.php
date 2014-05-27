<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectNoteCollection extends DataObjectCollection {
	
	protected $version = '$Revision: 1.1 $';
	
	public $field;
		
	function __construct($do = 'ProjectNote', $tablename = 'project_notes_overview')
	{
		parent::__construct($do, $tablename);
	}
		
}

// end of ProjectNoteCollection.php