<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/**
 * @see StatusSearchField
 * extended just with statuses
 */

class OrderStatusSearchField extends StatusSearchField
{
	
	protected $version = '$Revision: 1.3 $';
	
	/**
	 * $statuses array
	 * the status codes that ticket statuses can be given
	 * @TODO: this should really be a property of TicketStatus
	 */
	protected $statuses = array(
		'new',
		'approved',
		'rejected',
		'cancelled',
		'dispatched'
	);
		
}

// end of OrderStatusSearchField.php