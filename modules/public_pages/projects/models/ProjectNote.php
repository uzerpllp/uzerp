<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectNote extends DataObject {
	
	protected $version = '$Revision: 1.1 $';
	
	protected $defaultDisplayFields = array(
		'title',
		'type'
	);
	
	function __construct($tablename = 'project_notes')
	{
		
		parent::__construct($tablename);
		$this->idField = 'id';
		
 		$this->belongsTo('Project', 'project_id', 'project');
 		$this->belongsTo('ProjectNoteType', 'type_id', 'type');
 		
	}

}

// end of ProjectNote.php