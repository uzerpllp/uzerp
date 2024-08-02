<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SalesHistoryGrapher extends SimpleGraphEGlet {

	protected $version='$Revision: 1.9 $';

	protected $template = 'sorders_overview.tpl';

	function populate() {

		$orders				= new SInvoiceCollection(new SInvoice);
		$customersales 		= $orders->getSalesHistory();

		$db					= &DB::Instance();
		$param				= new GLParams();
		$currency_symbol	= (string) $param->base_currency_symbol();
		$options			= array();

		$options['header']['text']  = 'Sales this month '.$param->base_currency_symbol().$customersales['current']['this_month_to_date']['value'];
		$options['header']['text'] .= ' : this week '.$param->base_currency_symbol().$customersales['current']['this_week']['value'];
		$options['header']['text'] .= ' : last week '.$param->base_currency_symbol().$customersales['current']['last_week']['value'];

		$options['header']['textStyle']['font-size'] = '14pt';

		ksort($customersales['previous']);

		$options['date_axis'] = TRUE;

		$sales_counter = 0;

		// build up data array
		foreach ($customersales['previous'] as $period => $value) {

			$date = explode('/', $period);

			$data['x'][] = $date[1] . "/1/" . $date[0];
			$data['y'][] = (float) $value['value'];

			$label[0][$sales_counter++] = number_format($value['value'], 2);

		}

		$options['seriesList'][] = array(
			'legendEntry'	=> FALSE,
			'data' 			=> $data,
			'date_axis'		=> array('x' => TRUE),
			'markers'		=> array(
				'visible'	=> TRUE,
				'type'		=> 'circle'
			)
		);

		$options['type']		= 'line';
		$options['identifier']	= __CLASS__;
		$options['labels'] 		= $label;

		$this->contents = json_encode($options);

	}

}

// end of SalesHistoryGrapher.php
