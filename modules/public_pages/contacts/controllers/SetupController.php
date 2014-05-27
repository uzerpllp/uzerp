<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SetupController extends MasterSetupController 
{
	
	protected $version = '$Revision: 1.8 $';
	
	protected $setup_preferences = array('auto-account-numbering' => 'Auto-Create Account Numbers'
									);
		
	protected $setup_options = array('contact_categories'		=> 'Contactcategory'
									,'company_classifications'	=> 'CompanyClassification'
									,'company_industries'		=> 'CompanyIndustry'
									,'company_ratings'			=> 'CompanyRating'
									,'company_sources'			=> 'CompanySource'
									,'company_statuses'			=> 'CompanyStatus'
									,'company_types'			=> 'CompanyType'
									,'company_types'			=> 'CompanyType'
									);
	
	protected function registerPreference()
	{
		parent::registerPreference();
		
		$autoAccountNumbering = $this->module_preferences['auto-account-numbering']['preference'];
		
		$this->preferences->registerPreference(
				array(
					'name'			=> 'auto-account-numbering',
					'display_name'	=> $this->module_preferences['auto-account-numbering']['title'],
					'type'			=> 'checkbox',
					'status'		=> (empty($autoAccountNumbering) || $autoAccountNumbering == 'off') ? 'off' : 'on',
					'default'		=> 'off'
				)
		);
		
	}
	
}

// End of Contacts:SetupController
