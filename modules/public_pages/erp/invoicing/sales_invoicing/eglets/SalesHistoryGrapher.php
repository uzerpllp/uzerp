<?php

/**
 *	(c) 2024 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class SalesHistoryGrapher extends SimpleGraphEGlet
{
    protected $template = 'sorders_overview.tpl';

    public function populate()
    {
        $orders = new SInvoiceCollection(new SInvoice());
        $customersales = $orders->getSalesHistory();

        $param = new GLParams();
        $currency_symbol = (string) $param->base_currency_symbol();
        $options = array();

        $options['header']['text'] = 'Sales this month ' . $currency_symbol . $customersales['current']['this_month_to_date']['value'];
        $options['header']['text'] .= ' : this week ' . $currency_symbol . $customersales['current']['this_week']['value'];
        $options['header']['text'] .= ' : last week ' . $currency_symbol . $customersales['current']['last_week']['value'];

        ksort($customersales['previous']);

        // Build category and series arrays
        $x_axis_data = []; // periods
        $series_data = []; // total sales in currency
        foreach ($customersales['previous'] as $period => $value) {
            $date = explode('/', $period);
            $x_axis_data[] = "{$date[0]} - {$date[1]}";
            $series_data[] = round((float) $value['value']);
        }

        $options['type']		= 'echart'; //See: EgletGraphRenderer
        $options['identifier']	= __CLASS__;
		$click_url = link_to(['module' => 'sales_invoicing', 'controller' => 'sinvoices'], html: \false);
        $options['clickAction'] = "{$click_url}&from={from}&to={to}";
        $chart_options = new echartOptions(xData: $x_axis_data, series: $series_data);
        $chart_options->setOption('yAxis', ['type' => 'value', 'axisLabel' => ['formatter' => '£{value}']]);
        $chart_options->setOption('tooltip', ['formatter' => '{b}<br><strong>£{c}</strong>']);
        $options['echart'] = $chart_options->getOptionsArray();
		
        $this->contents = json_encode($options);
    }
}
