{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$Person}
				<dt class="heading">Person Details</dt>
				{view_data attribute='fullname'}
				{view_data attribute='company'}
				{view_data attribute='person_assigned_to'}
				{if "categories"|prettify <> 'EGS_HIDDEN_FIELD'}
					<dt>{"categories"|prettify}</dt>
					{foreach name=categories item=category from=$categories}
						{if $smarty.foreach.categories.first}<dd>{/if}
						{$category->category}{if !$smarty.foreach.categories.last},{/if}
					{foreachelse}
						<dd class="blank">-
					{/foreach}		
					</dd>
				{/if}
				<dt class="heading">Job Details</dt>
					{view_data attribute='jobtitle'}
					{view_data attribute='person_reports_to' fk='person' fk_field='reports_to'}
					{view_data attribute='department'}
					{view_data attribute='language'}
			
				<dt class="heading">Contact History</dt>
				{with model=$Person}
					{view_data attribute='created'}
					{view_data attribute='lastupdated'}
				{/with}
			{/with}
		</dl>
		<dl id="view_data_right">
			{with model=$Person->main_address->address}
				<dt class="heading">Address Details</dt>
				{view_data attribute="street1"}
				{view_data attribute="street2"}
				{view_data attribute="street3"}
				{view_data attribute="town"}
				{view_data attribute="county"}
				{view_data attribute="postcode"}
				{view_data attribute="country"}
			{/with}
			<dt class="heading">Contact Details</dt>
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
		</dl>
	</div>
{/content_wrapper}