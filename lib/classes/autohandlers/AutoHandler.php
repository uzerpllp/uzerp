<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
abstract class AutoHandler {
	private $onupdate;
	
	public function __construct($onupdate=false) {
		$this->onupdate=$onupdate;
	}

	abstract public function handle(DataObject $model);
	
}
?>