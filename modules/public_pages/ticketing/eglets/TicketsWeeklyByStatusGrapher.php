<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketsWeeklyByStatusGrapher extends SimpleGraphEGlet {

	protected $version='$Revision: 1.4 $';
	
	function populate() {
		
		// set vars
		$results_array	= array();
		$options		= array();
		
		$query = 'SELECT s.name as status, count(t.id) as total 
		FROM tickets t, ticket_statuses s 
		WHERE t.internal_ticket_status_id=s.id 
		AND t.usercompanyid='.EGS_COMPANY_ID.' 
		AND ((s.status_code<>\'CLSD\') OR (s.status_code=\'CLSD\' AND extract(\'week\' FROM t.lastupdated)=extract(\'week\' from now()))) 
		GROUP BY s.name, s.index ORDER BY s.index DESC';
		
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

// end of TicketsWeeklyByStatusGrapher.php