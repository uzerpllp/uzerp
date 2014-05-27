<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class IndexController extends DashboardController
{

	protected $version='$Revision: 1.6 $';
	
	public function index ()
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
