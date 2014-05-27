<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
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
		
}

// End of SetupController
