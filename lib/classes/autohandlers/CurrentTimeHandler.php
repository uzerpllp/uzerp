<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CurrentTimeHandler extends AutoHandler {
	
	function handle(DataObject $model) {
		return 'now()';
	}

}
?>
