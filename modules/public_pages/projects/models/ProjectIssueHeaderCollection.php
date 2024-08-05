<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectIssueHeaderCollection extends DataObjectCollection {

	protected $version = '$Revision: 1.1 $';

	public $field;

	function __construct($do = 'ProjectIssueHeader', $tablename = 'project_issue_header_overview')
	{
		parent::__construct($do, $tablename);
	}

}

// end of ProjectIssueHeaderCollection.php