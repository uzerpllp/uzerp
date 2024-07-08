<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class IndexController extends DashboardController
{

	protected $version='$Revision: 1.6 $';
	
	public function index($collection = null, $sh = '', &$c_query = null)
	{
		
		$glparams = DataObjectFactory::Factory('GLParams');
		
		if (is_null($glparams->ar_disposals_proceeds_account())
		|| is_null($glparams->ar_disposals_proceeds_centre())
		|| is_null($glparams->ar_pl_suspense_account())
		|| is_null($glparams->ar_pl_suspense_centre()))
		{
			$flash = Flash::Instance();
			$flash->addError('GL Support is not enabled');
		}
		
		parent::index();
	
	}
	
}

// End of IndexController
