<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class OpportunitiesHistoryGrapher extends SimpleGraphEGlet {
	
	protected $version='$Revision: 1.3 $';
		
	function populate() {
		
		$query		= 'select extract(\'month\' FROM o.enddate) AS month, sum(o.cost) AS total FROM opportunities o, opportunitystatus s WHERE o.status_id=s.id AND s.usercompanyid='.EGS_COMPANY_ID.' AND s.open=false AND s.won=true AND o.assigned=\''.EGS_USERNAME.'\' AND o.enddate>=(now()-interval \'1 year\') GROUP BY extract(\'month\' FROM o.enddate), extract(\'year\' FROM o.enddate) ORDER BY extract(\'year\' FROM o.enddate), extract(\'month\' FROM o.enddate)';
		$db			= &DB::Instance();
		$result		= $db->GetAssoc($query);
		$options	= array();
		
		// no point in continuing if we've got no data to play with
		if (empty($result)) {
			return FALSE;
		}
		
		foreach($result as $name => $cost) {
			$options['data']['x'][] = $name;
			$options['data']['y'][] = (float) $cost;
		}
		
		// need an options array
		$options['legendEntry'] = FALSE;
		
		$this->contents = array(
			'type'			=> 'line',
			'identifier'	=> __CLASS__,
			'options'		=> json_encode($options)
		);
		
	}
	
}

// end of OpportunitiesHistoryGrapher.php