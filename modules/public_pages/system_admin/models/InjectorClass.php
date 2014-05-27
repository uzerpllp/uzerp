<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class InjectorClass extends DataObject {

	function __construct($tablename='injector_classes') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->validateUniquenessOf('name', 'class_name');
		$this->identifierField='name';
		$this->orderby = 'name';
		$this->setEnum('category', array('SY'=>'System'
										,'PT'=>'Payment Type'
										,'RP'=>'Report Type'
										,'WO'=>'Works Order'));
	}

	public static function unserialize ($serialized_value) {
		$classes=array();
		$values=unserialize($serialized_value);
		if (!is_array($values)) {
			$values=array($values);
		}
		foreach ($values as $key) {
			$injector=new InjectorClass();
			$injector->load($key);
			if ($injector) {
				$classes[$key]=$injector;
			}
		}
		return $classes;
	}
	
}
?>
