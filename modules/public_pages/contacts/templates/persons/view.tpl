{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.8 $ *}	
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl class="float-left">
			{with model=$Person}
				{view_section heading="Person Details" expand='open'}
					{view_data attribute='fullname'}
					{view_data attribute='company'}
					{view_data attribute='person_assigned_to'}
					{view_data attribute='end_date'}
					{view_data attribute="categories" value=$categories}
				{/view_section}
				
				{view_section heading="Job Details" expand='open'}
					{view_data attribute='jobtitle'}
					{view_data attribute='person_reports_to' fk='person' fk_field='reports_to'}
					{view_data attribute='department'}
					{view_data attribute='language'}
				{/view_section}
			
				{view_section heading="Contact Details" expand='open'}
					{with model=$Person->phone->contactmethod}
						{view_data attribute="contact" label="phone"}
					{/with}
					{with model=$Person->mobile->contactmethod}
						{view_data attribute="contact" label="mobile"}
					{/with}
					{with model=$Person->fax->contactmethod}
						{view_data attribute="contact" label="fax"}
					{/with}
					{with model=$Person->email->contactmethod}
						{view_data attribute="contact" label="email"}
					{/with}
				{/view_section}
			{/with}
		</dl>
		<dl class="float-right">
			{with model=$Person->main_address->address}
				{view_section heading="Address Details" expand='open'}
					{view_data attribute="street1"}
					{view_data attribute="street2"}
					{view_data attribute="street3"}
					{view_data attribute="town"}
					{view_data attribute="county"}
					{view_data attribute="postcode"}
					{view_data attribute="country"}
				{/view_section}
			{/with}
			
			{with model=$Person}
				{view_section heading="Contact Criteria" expand='open'}
					{view_data attribute="can_call"}	
					{view_data attribute="can_email"}	
				{/view_section}
			
				{view_section heading="Contact History" expand='closed'}
					{view_data attribute='created'}
					{view_data attribute='owner'}
					{view_data attribute='lastupdated'}
					{view_data attribute='alteredby'}
				{/view_section}
			{/with}
		</dl>
	</div>
{/content_wrapper}