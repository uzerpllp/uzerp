<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CurrentUserHandler extends AutoHandler {
	protected $default_username;
	protected $constant;

	public function __construct($onupdate=false, $username_constant=null) {
		parent::__construct($onupdate);
		$this->constant=$username_constant;
	}

	function handle(DataObject $model) {
		return constant($this->constant);
	}
}
?>