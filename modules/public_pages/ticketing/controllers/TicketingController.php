<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketingController extends Controller {

	protected $version='$Revision: 1.1 $';
	
	public function save() {
		if (parent::save($this->modeltype)) {
			sendTo($this->name, 'view', $this->_modules, array('id'=>$this->saved_model->id));
		}
		$this->refresh();
	}
	
	public function delete () {
		// Set some defaults
		parent::delete($this->modeltype);
		sendTo($this->name, 'index', $this->_modules);
	}
	
}
?>