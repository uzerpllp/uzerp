{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.8 $ *}	
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
		{with model=$Lead}
			{view_section heading="account_details"}
				{view_data attribute="name"}
				{view_data attribute="owner"}
				{view_data attribute="assigned"}
				{view_data attribute="parent"}
				{view_data attribute="categories" value=$categories}
			{/view_section}
			{view_section heading="organisation_details"}
				{view_data attribute="vatnumber"}
				{view_data attribute="companynumber"}
				{view_data attribute="employees"}
			{/view_section}
			{if $crm_access}
				<dt class="heading">
					CRM Details
				</dt>
				{view_data attribute="company_status"}
				{view_data attribute="company_source"}
				{view_data attribute="company_classification"}
				{view_data attribute="company_rating"}
				{view_data attribute="company_industry"}
				{view_data attribute="company_type"}
			{/if}
		{/with}
		</dl>
		<dl id="view_data_right">
			{view_section heading="address_details"}
				{with model=$Lead->main_address->address}
					{view_data attribute="street1"}
					{view_data attribute="street2"}
					{view_data attribute="street3"}	
					{view_data attribute="town"}
					{view_data attribute="county"}
					{view_data attribute="postcode"}
					{view_data attribute="country"}
				{/with}
			{/view_section}
			{view_section heading="contact_details"}
				{with model=$Lead->phone->contactmethod}
					{view_data attribute="contact" label="phone"}
				{/with}
				{with model=$Lead->fax->contactmethod}
					{view_data attribute="contact" label="fax"}
				{/with}
				{with model=$Lead->email->contactmethod}
					{view_data attribute="contact" label="email"}
				{/with}
				{with model=$Lead}
					{view_data attribute="website"}
				{/with}
			{/view_section}
			{view_section heading="access_details"}
				{with model=$Lead}
					{view_data attribute="created"}
					{view_data attribute="lastupdated"}
				{/with}
			{/view_section}
		</dl>
	</div>
{/content_wrapper}