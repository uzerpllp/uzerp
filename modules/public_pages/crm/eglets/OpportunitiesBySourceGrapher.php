<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OpportunitiesBySourceGrapher extends SimpleGraphEGlet {

	protected $version='$Revision: 1.3 $';

	function populate() {
		$db	= &DB::Instance();

		$query = $db->prepare("select s.name, COALESCE(sum(o.cost), 0.0) AS piplinecost FROM opportunitysource s LEFT OUTER JOIN opportunities o ON (s.id=o.source_id
		AND o.usercompanyid=? AND extract('month' FROM o.enddate)=extract('month' FROM now())
		AND extract('year' FROM o.enddate)=extract('year' FROM now())) WHERE s.usercompanyid=? GROUP BY s.name ORDER BY COALESCE(sum(o.cost), 0.0), s.name");

		$vars = [EGS_COMPANY_ID, EGS_COMPANY_ID];
		$result		= $db->GetAssoc($query, $vars);

		$options	= [];
		// Horizontal Barchart
		// Categories to the left
		$yAxis  = ['type' => 'category'];
		// Values along the bottom
		$xAxis = ['type' => 'value'];
		// Series type for barchart
		$series = ['type' => 'bar'];
		// Add the data to the prepared arrays
		foreach($result as $name => $cost) {
			$yAxis['data'][] = $name;
			$series['data'][] = (float) $cost;
		}

		$options['type']		= 'echart';
		$options['identifier']	= __CLASS__;

		$chart_options = new echartOptions();
		$chart_options->setOption('xAxis', $xAxis);
		$chart_options->setOption('yAxis', $yAxis);
		$chart_options->setOption('series', $series); 
		$options['echart'] = $chart_options->getOptionsArray();
		$this->contents = json_encode($options);
	}

}
