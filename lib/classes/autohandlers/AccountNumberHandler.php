<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AccountNumberHandler extends AutoHandler
{

	protected $version = '$Revision: 1.4 $';
	
	function handle(DataObject $model)
	{
		// Do we want to generate an account number?
		$system_prefs = SystemPreferences::instance();
		$autoGenerate = $system_prefs->getPreferenceValue('auto-account-numbering', 'contacts');
		
		if(!(empty($autoGenerate) || $autoGenerate === 'off'))
		{
			// Obviously not.
			return false;
		}

		if(isset($model->accountnumber))
		{
			// Account number already filled in, so just return.
			return false;
		}
		
		return $model->createAccountNumber();
		
	}
}

// End of AccountNumberHandler
