<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketsWeeklyBySeverityGrapher extends SimpleGraphEGlet {

	protected $version='$Revision: 1.3 $';
	
	function populate() {
		
			
		// set vars
		$results_array	= array();
		$options		= array();
		
		// ATTN: needs converting
		$query='SELECT s.name as severity, count(t.id) as total 
		FROM tickets t, ticket_severities s, ticket_statuses as h 
		WHERE t.usercompanyid='.EGS_COMPANY_ID.' 
		AND t.internal_ticket_severity_id=s.id 
		AND t.internal_ticket_status_id=h.id 
		AND h.status_code<>\'CLSD\' GROUP BY s.name, s.index ORDER BY s.index ASC';
		
		// get results
		$db			= &DB::Instance();
		$results	= $db->GetAssoc($query);
		
		// build array, jsoning as we go
		foreach ($results as $name => $cost) {
			
			$options['seriesList'][] = array(
				'label'			=> $name,
				'legendEntry'	=> true,
				'data'			=> (float) $cost,
				'offset'		=> 0
			);
			
		}

		$options['type']		= 'pie';
		$options['identifier']	= __CLASS__;
		
		$this->contents = json_encode($options);
		
	}
	
}

// end of TicketsWeeklyBySeverityGrapher.php