<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketingController extends Controller {

	protected $version='$Revision: 1.1 $';
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		if (parent::save($this->modeltype)) {
			sendTo($this->name, 'view', $this->_modules, array('id'=>$this->saved_model->id));
		}
		$this->refresh();
	}
	
	public function delete($modelName = null) {
		// Set some defaults
		parent::delete($this->modeltype);
		sendTo($this->name, 'index', $this->_modules);
	}
	
}
?>