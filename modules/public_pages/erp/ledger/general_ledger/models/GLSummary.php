<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLSummary extends DataObject {

	protected $version='$Revision: 1.4 $';
	
	function __construct($tablename='gl_summaries') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField='summary || \' - \' || sub_group';
		$this->orderby='summary, sub_group';
		$this->validateUniquenessOf(array('summary', 'sub_group'));
	}

}
?>