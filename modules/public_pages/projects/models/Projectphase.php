<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Projectphase extends DataObject {
	
	protected $defaultDisplayFields=array('name'
										 ,'position'
										 );
	
	function __construct($tablename='project_phases') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->identifierField='name';
		
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Project', 'project_id', 'project');
 		$this->belongsTo('Project', 'project_id', 'project'); 

	}

}
?>