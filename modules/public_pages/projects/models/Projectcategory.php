<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Projectcategory extends DataObject {

	function __construct($tablename='project_categories') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->identifierField='name';
		
 		$this->validateUniquenessOf('id'); 

	}

}
?>