<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
interface TaxCalculation {
	public function calc_percentage($rate_id,$status_id,$amount);	
}
?>