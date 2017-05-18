<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Prettifier implements Translation {

	protected $version = '$Revision: 1.5 $';
	
	protected $acronyms = array(
		'crm'			=> 'CRM',
		'erp'			=> 'ERP',
		'dob'			=> 'DOB',
		'ni'			=> 'NI',
		'url'			=> 'URL',
		'ecommerce'		=> 'eCommerce',
		'accounts/erp'	=> 'Accounts/ERP',
		'erp setup'		=> 'ERP Setup',
		'vat_number'	=> 'VAT Number'
	);

	protected $over_ride = array(
		'companyaddresses'		=> 'company_addresses',
		'companycontactmethods'	=> 'company_contact_methods',
		'websiteadmins'			=> 'website_admins',
		'webpagecategories'		=> 'webpage_categories',
		'webpagerevisions'		=> 'webpage_revisions',
		'systemcompanies'		=> 'system_companies',
		'countrycode'			=> 'country',
		'lastupdated'			=> 'last_updated',
		'startdate'				=> 'start_date',
		'enddate'				=> 'end_date',
		'accountnumber'			=> 'account_number',
		'creditlimit'			=> 'credit_limit',
		'vatnumber'				=> 'vat_number',
		'companynumber'			=> 'company_number',
		'usercompanyaccesses'	=> 'user_company_access',
		'websitefiles'			=> 'website_files',
		'fullname'				=> 'full_name',
		'intranetsection'		=> 'intranet_section',
		'intranetpage'			=> 'intranet_page',
		'websiteadmin'			=> 'website_admin',
		'calendarevent'			=> 'calendar_event'
	);
	
	protected $known_replacements = array(
		'uzlet' => 'uzLET'
	);
	
	function translate($string)
	{
		
		// return, if set, the acronym for a string
		if (isset($this->acronyms[strtolower($string)]))
		{
			return $this->acronyms[strtolower($string)];
		}
		
		// return, if set, the over ride for a string
		if (isset($this->over_ride[strtolower($string)]))
		{
			return prettify($this->over_ride[strtolower($string)]);
		}
		
		// capatilse, replace "_" with " " and remove "_id" at end of string, apply this to $string
		$string = (substr($string, -3)=='_id')?substr($string, 0, -3):$string;
		$string = ucwords(str_replace('_', ' ', $string));
		
		/*
		 * Because some words may not exist atomically (such as the word CRM might) 
		 * we need to find specific words and replace them with their known output, 
		 * such as Uzlet -> uzLET	
		 */
		foreach ($this->known_replacements as $find => $replace)
		{
			$string = str_ireplace($find, $replace, $string);
		}
		
		// return the prettified word
		return $string;
		
	}

}

// end of Prettifier.php