<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OpportunitiesMonthlyByStatusGrapher extends SimpleGraphEGlet {

	protected $version='$Revision: 1.3 $';

	function populate() {

		$db			= &DB::Instance();
		$query		= 'SELECT s.name, COALESCE(sum(o.cost),0) AS pipelinecost FROM opportunitystatus s LEFT OUTER JOIN opportunities o ON (s.id=o.status_id AND o.usercompanyid='.EGS_COMPANY_ID. ' AND extract(\'month\' FROM o.enddate)=extract(\'month\' FROM now()) AND extract(\'year\' FROM o.enddate)=extract(\'year\' FROM now())) AND o.assigned='.$db->qstr(EGS_USERNAME).' WHERE s.usercompanyid='.EGS_COMPANY_ID.'GROUP BY s.name, s.position ORDER BY s.position DESC';
		$result		= $db->GetAssoc($query);
		$options	= array();
		$data		= array();

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

// end of OpportunitiesMonthlyByStatusGrapher.php