<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WHActionCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.5 $';
	
	public $field;
		
	function __construct($do = 'WHAction', $tablename = 'wh_actions_overview') {
		parent::__construct($do, $tablename);
			
	}

}

// End of WHActionCollection
