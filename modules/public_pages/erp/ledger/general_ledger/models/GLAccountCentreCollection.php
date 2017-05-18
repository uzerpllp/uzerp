<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLAccountCentreCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.7 $';
	public $field;
		
	function __construct($do='GLAccountCentre', $tablename='glaccountcentresoverview') {
		parent::__construct($do, $tablename);
	    $this->identifierField = 'glaccount_centre';
 	    
	}

}
?>