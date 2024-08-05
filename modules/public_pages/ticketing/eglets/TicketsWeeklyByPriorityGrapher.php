<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketsWeeklyByPriorityGrapher extends SimpleGraphEGlet {

	protected $version='$Revision: 1.3 $';

	function populate() {

		// set vars
		$results_array	= array();
		$options		= array();

		// ATTN: needs converting
		$query='SELECT p.name as priority, count(t.id) as total 
		FROM tickets t, ticket_priorities p, ticket_statuses as h 
		WHERE t.usercompanyid='.EGS_COMPANY_ID.' 
		AND t.internal_ticket_priority_id=p.id 
		AND t.internal_ticket_status_id=h.id 
		AND h.status_code<>\'CLSD\' GROUP BY p.name, p.index ORDER BY p.index ASC';

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

// end of TicketsWeeklyByPriorityGrapher.php