<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SetupController extends MasterSetupController
{

	protected $version = '$Revision: 1.4 $';
	
	protected $setup_options = array(
			'sl_analysis'=>'SLAnalysis',
			'sy_delivery_terms'=>'DeliveryTerm',
			'intrastat_trans_types'=>'IntrastatTransType'
		);
	
	protected $setup_preferences = [
		'sales-invoice-report-type' => 'Report type to be used for Sales Invoice print layout options',
	];

	protected function registerPreference()
	{
		parent::registerPreference();

		$salesInvoiceReportType = $this->module_preferences['sales-invoice-report-type']['preference'];

		$reportTypes = new ReportType();
		$types = $reportTypes->getPrivateReportTypes();
		$data = [];
		$data[] = ['label' => 'None', 'value' => ''];
        foreach ($types as $key => $item) {
            $data[] = ['label' => $item, 'value' => $key];
        }

		$this->preferences->registerPreference(array(
			'name' => 'sales-invoice-report-type',
			'display_name' => $this->module_preferences['sales-invoice-report-type']['title'],
			'type' => 'select',
			'data' => $data,
			'value' => $salesInvoiceReportType,
			'default' => ''
		));
	}
}

// End of SetupController
