<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SystemcompanyCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('Systemcompany');
			$this->_tablename="system_companiesoverview";
			$this->identifierField='company';
		}
	
		function getCompanies() {
			$sh=new SearchHandler($this, false); 
			$sh->setFields(array('company_id','company'));
			$this->load($sh);
			return $this->getAssoc();
		}
		
		
		
}
?>
