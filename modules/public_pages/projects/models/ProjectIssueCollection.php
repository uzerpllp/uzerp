<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectIssueCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.4 $';
	
	public $field;
		
	function __construct($do='ProjectIssue', $tablename='project_issuesoverview') {
		parent::__construct($do, $tablename);
			
	}
		
}
?>