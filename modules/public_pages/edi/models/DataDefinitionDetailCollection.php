<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataDefinitionDetailCollection extends DataObjectCollection {

	protected $version='$Revision: 1.5 $';
	
	function __construct($do='DataDefinitionDetail', $tablename='data_definition_details_overview') {
// Contruct the object
		parent::__construct($do, $tablename);
		$this->orderby='position';
		
	}

	function DTD ($_type, $_start_element) {
		
	}
	
	static function getDefinitionTree ($definition_id, $parent_id=null) {
		$nextlevel=new DataDefinitionDetailCollection(new DataDefinitionDetail);
		$sh=new SearchHandler($nextlevel, false);
		$sh->addConstraint(new Constraint('data_definition_id', '=' , $definition_id));
		
		if (empty($parent_id)) {
			$sh->addConstraint(new Constraint('parent_id', 'is', 'NULL'));
		} else {
			$sh->addConstraint(new Constraint('parent_id', '=', $parent));
		}
		$sh->setOrderby('position');
		$nextlevel->load($sh);

		return $nextlevel;
	}
	
}
?>