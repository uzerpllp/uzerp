<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Taskpriority extends DataObject {

	function __construct($tablename='task_priorities') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->identifierField='name';
		
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Project', 'project_id', 'project'); 

	}

}
?>