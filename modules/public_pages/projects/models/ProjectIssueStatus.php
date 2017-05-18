<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectIssueStatus extends DataObject {

	function __construct($tablename='project_issue_statuses') {
		parent::__construct($tablename);
			$this->idField='id';
 
	}

}
?>