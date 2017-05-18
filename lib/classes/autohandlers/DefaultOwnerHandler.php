<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DefaultOwnerHandler extends AutoHandler {
	protected $default_username;
	public function __construct($onupdate=false,$username) {
		parent::__construct($onupdate);
		$this->default_username=$username;
	}

	function handle(DataObject $model) {
		return $this->default_username;
	}
}
?>