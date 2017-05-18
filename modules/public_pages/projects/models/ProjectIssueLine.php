<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectIssueLine extends DataObject {
	
	protected $version = '$Revision: 1.1 $';
	
	protected $defaultDisplayFields = array(
		'title'
	);
	
	function __construct($tablename = 'project_issue_lines')
	{
		
		parent::__construct($tablename);
		$this->idField = 'id';
		
		$this->belongsTo('User', 'completed_by', 'completedby');
 		$this->hasOne('ProjectIssueHeader', 'header_id', 'header');

	}

}

// end of ProjectIssueLine.php