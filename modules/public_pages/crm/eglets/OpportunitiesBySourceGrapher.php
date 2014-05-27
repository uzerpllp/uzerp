<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OpportunitiesBySourceGrapher extends SimpleGraphEGlet {
	
	protected $version='$Revision: 1.3 $';
	
	function populate() {
		
		$query='select s.name, COALESCE(sum(o.cost), 0.0) AS piplinecost FROM opportunitysource s LEFT OUTER JOIN opportunities o ON (s.id=o.source_id
		AND o.usercompanyid='.EGS_COMPANY_ID.
		' AND extract(\'month\' FROM o.enddate)=extract(\'month\' FROM now())
		AND extract(\'year\' FROM o.enddate)=extract(\'year\' FROM now())) WHERE s.usercompanyid='.EGS_COMPANY_ID.' GROUP BY s.name ORDER BY COALESCE(sum(o.cost), 0.0), s.name';

		$db			= &DB::Instance();
		$result		= $db->GetAssoc($query);
		$options	= array();
		
		foreach($result as $name => $cost) {
			$data['x'][] = $name;
			$data['y'][] = (float) $cost;
		}
		
		$options['seriesList'][] = array(
			'label'			=> '',
			'legendEntry'	=> FALSE,
			'data'			=> $data
		);
		
		$options['type']		= 'bar';
		$options['identifier']	= __CLASS__;
		
		$this->contents = json_encode($options);
		
	}
	
}

// end of OpportunitiesBySourceGrapher.php