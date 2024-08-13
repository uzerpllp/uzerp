<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CustomerServiceGrapher extends SimpleGraphEGlet {

	protected $template = 'Customer_Service.tpl';

	function populate() {

		$orders			= new CustomerServiceCollection(new SInvoiceLine);
		$customersales	= $orders->getServiceHistory();

		$category = [];

		// Adding three datasets to the series. Each one must be named
		// and have a type set.
		$ontime_data = ['name' => 'On Time', 'type' => 'bar', 'tooltip' => ['formatter' => '{b}<br>{a} <strong>{c}</strong>%']];
		

		foreach ($customersales['previous'] as $period => $value) {
			$date = explode('/', $period);
			$category[] = "{$date[0]} - {$date[1]}";
			// Add data-points to the series.
			$ontime_data['data'][] = round((float) $value['ontime%']);
		}

		$infull_data = ['name' => 'In Full', 'type' => 'bar', 'tooltip' => ['formatter' => '{b}<br>{a} <strong>{c}</strong>%']];
		foreach ($customersales['previous'] as $period => $value) {
			$infull_data['data'][] = round((float) $value['infull%']);
		}

		$otinfull_data = ['name' => 'On Time & In Full', 'type' => 'bar', 'tooltip' => ['formatter' => '{b}<br>{a} <strong>{c}</strong>%']];
		foreach ($customersales['previous'] as $period => $value) {
			$otinfull_data['data'][] = round((float) $value['ontime_infull%']);
		}

		$options['type']		= 'echart';
		$options['identifier']	= __CLASS__;

		// Create the chart options object, adding the category data and the three datasets to the series.
		$chart_options = new echartOptions(xData: $category, series: [$ontime_data, $infull_data, $otinfull_data]);
		// Format yAxis labels.
		$chart_options->setOption('yAxis', ['axisLabel' => ['formatter' => '{value}%']]);
		// Turn on the chart legend.
		$chart_options->setOption('legend', new stdClass());
		// Postition the chart inside the uzlet container.
		$chart_options->setOption('grid', [
            'left' => '10%',
            'top' => '10%',
            'right' => '10%',
            'bottom' => '70px'
        ]);
		
		$options['echart'] = $chart_options->getOptionsArray();
		$this->contents = json_encode($options);
	}
}
