<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EGletContainer extends Dashboard {

	protected $version='$Revision: 1.2 $';
	
	public $eglet;
	
	function addEGlet($name,EGlet $eglet) {
		parent::addEGlet($name,$eglet);
		$this->eglet=$eglet;
	}

	function render($params,&$smarty) {
		$this->eglet->setSmarty($smarty);
		$smarty->assign('eglet',$this->eglet);
		$smarty->display('elements/inline_dashboard.tpl');
	}
	
	
}
?>