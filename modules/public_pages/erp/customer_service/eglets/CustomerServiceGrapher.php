<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CustomerServiceGrapher extends SimpleGraphEGlet {

	protected $version='$Revision: 1.4 $';

	protected $template = 'Customer_Service.tpl';

	function populate() {

		$orders			= new CustomerServiceCollection(new SInvoiceLine);
		$customersales	= $orders->getServiceHistory();

		$category = [];
		$ontime_data = ['name' => 'On Time', 'type' => 'bar'];
		foreach ($customersales['previous'] as $period => $value) {
			$category[] = $period;
			$ontime_data['data'][] = round((float) $value['ontime%']);
		}

		$infull_data = ['name' => 'In Full', 'type' => 'bar'];
		foreach ($customersales['previous'] as $period => $value) {
			$infull_data['data'][] = round((float) $value['infull%']);
		}

		$otinfull_data = ['name' => 'On Time & In Full', 'type' => 'bar'];
		foreach ($customersales['previous'] as $period => $value) {
			$otinfull_data['data'][] = round((float) $value['ontime_infull%']);
		}

		$options['type']		= 'echart';
		$options['identifier']	= __CLASS__;

		$chart_options= new echartOptions(xData: $category, series: [$ontime_data, $infull_data, $otinfull_data]);
		$chart_options->setOption('legend', new stdClass());
		$options['echart'] = $chart_options->getOptionsArray();
		$this->contents = json_encode($options);
	}
}
