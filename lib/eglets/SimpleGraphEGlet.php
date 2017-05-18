<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
abstract class SimpleGraphEGlet extends SimpleEGlet {

	protected $version='$Revision: 1.3 $';
	
	function checkSetup() {
		return true;
	}
	
	function setType($type) {
		$this->type=$type;
	}

	function getClassName() {
		return 'eglet double_eglet';
	}
	
	function getSource() {
		return '/data/tmp/chart'.$this->id;
	}
	
	static function getRenderer() {
		return new EgletGraphRenderer();
	}
}
?>