<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CurrentUserHandler extends AutoHandler {
	protected $default_username;
	public function __construct($onupdate=false,$username_constant) {
		parent::__construct($onupdate);
		$this->constant=$username_constant;
	}

	function handle(DataObject $model) {
		return constant($this->constant);
	}
}
?>