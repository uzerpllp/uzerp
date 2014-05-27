<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WHLocationCollection extends DataObjectCollection {

	protected $version='$Revision: 1.4 $';
	
	public $field;
	
	function __construct($do='WHLocation', $tablename='wh_locationsoverview') {
		parent::__construct($do, $tablename);
		
	}

	function getLocationList($cc="") {
		$sh=new SearchHandler($this, false);
		if($cc instanceof ConstraintChain) {
			$sh->addConstraintChain($cc);
		}
		$sh->setOrderby(array('whstore', 'location'));
		$this->load($sh);
		$list=array();
		if ($this->count()>0) {
			foreach ($this as $location) {
				$list[$location->id]=$location->whstore.'/'.$location->location.'-'.$location->description;
			}
		}
		return $list;
	}
	
}
?>