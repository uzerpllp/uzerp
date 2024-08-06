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

		$db = &DB::Instance();

		$types = array(
			'ontime'		=> 'On Time',
			'infull'		=> 'In Full',
			'ontime_infull'	=> 'On Time / In Full'
		);

		$label = array();

		$type_counter	= 0;
		$sales_counter	= 0;

		foreach ($types as $key => $title) {

			$data = array();
			$sales_counter = 0;

			foreach ($customersales['previous'] as $period => $value) {

				$data['x'][] = $period;
				$data['y'][] = (float) number_format($value[$key . '%'], 2);

				$label[$type_counter][$sales_counter] = $title . ': ' . number_format($value[$key . '%'], 2);

				$sales_counter++;

			}

			$options['seriesList'][] = array(
				'label'			=> $title,
				'legendEntry'	=> TRUE,
				'data'			=> $data,
				'markers'		=> array(
					'visible'	=> TRUE,
					'type'		=> 'circle'
				)
			);

			$type_counter++;

		}

		$options['type']		= 'line';
		$options['identifier']	= __CLASS__;
		$options['labels']		= $label;

		$this->contents = json_encode($options);

	}

}

// end of CustomerServiceGrapher.php