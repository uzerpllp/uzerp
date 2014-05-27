<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SetupController extends MasterSetupController
{

	protected $version='$Revision: 1.8 $';
	
	protected $setup_options = array('opportunity_sources'		=> 'Opportunitysource'
									,'opportunity_types'			=> 'Opportunitytype'
									,'opportunity_statuses'		=> 'Opportunitystatus'
									,'spacer'
									,'activity_types'			=> 'Activitytype'
									,'spacer'
									,'campaign_types'			=> 'Campaigntype'
									,'campaign_statuses'		=> 'Campaignstatus'
									,'spacer'
									,'contact_categories'		=> 'Contactcategory'
									,'company_classifications'	=> 'CompanyClassification'
									,'company_industries'		=> 'CompanyIndustry'
									,'company_ratings'			=> 'CompanyRating'
									,'company_sources'			=> 'CompanySource'
									,'company_statuses'			=> 'CompanyStatus'
									,'company_types'			=> 'CompanyType'
									);
			
}

// End of CRM:SetupController
