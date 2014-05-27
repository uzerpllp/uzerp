<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SODespatchEvent extends DataObject {
	
	protected $version='$Revision: 1.3 $';
	
	function __construct($tablename='so_despatchevents') {
		parent::__construct($tablename);
		
 		$this->setEnum('status'
							,array('TC'=>'Time Confirmed'
								  ,'TNC'=>'Time Not Confirmed'
								  ,'NBI'=>'Not Booked In'
								  ,'LED'=>'Late / Early Delivery'
								)
						);
		
	}

}
?>