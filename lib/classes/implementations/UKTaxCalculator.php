<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class UKTaxCalculator implements TaxCalculation {

	protected $version='$Revision: 1.3 $';
	
	public function calc_percentage($rate_id,$status_id,$amount) {
		$rate = new TaxRate();
		$rate->load($rate_id);
// If no rate supplied then return zero		
		if (!$rate->isLoaded()) { return 0; }
		
		$rate_percentage = $rate->percentage;
		
		$status = new TaxStatus();
		$status->load($status_id);
// If no status supplied then by default apply tax		
		if (!$status->isLoaded()) { $status->apply_tax='t'; }
		
		if($status->apply_tax==='t') {
			$percentage = $rate_percentage;
		}
		else  {
			$percentage = 0;
		}
		return bcdiv($percentage,100,4);
		
	}
}
?>