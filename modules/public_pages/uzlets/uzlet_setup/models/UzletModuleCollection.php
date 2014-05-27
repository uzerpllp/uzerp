<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class UzletModuleCollection extends DataObjectCollection
{
	
	protected $version='$Revision: 1.3 $';
	
	public $field;
		
	function __construct($do='UzletModule', $tablename='uzlet_modules')
	{
		
		parent::__construct($do, $tablename);
		
		$this->title='uzLet Modules';
		
	}

}

// End of UzletModuleCollection
