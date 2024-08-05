<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectIssueHeader extends DataObject {

	protected $version = '$Revision: 1.1 $';

	protected $defaultDisplayFields = array(
		'title',
		'status'
	);

	function __construct($tablename = 'project_issue_header')
	{

		parent::__construct($tablename);

		$this->idField = 'id';

 		$this->belongsTo('Project', 'project_id', 'project');
		$this->hasMany('ProjectIssueLine', 'lines', 'header_id');

 		$this->setEnum(
 			'status',
			array(
				'O' => 'Open',
				'C' => 'Closed'
			)
 		);

	}

}

// end of ProjectIssueHeader.php