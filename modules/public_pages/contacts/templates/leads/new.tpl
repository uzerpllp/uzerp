{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.10 $ *}	
{content_wrapper}
	{form controller="leads" action="save"}
		{with model=$Lead->party}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='type' value='Company'}
		{/with}
		{with model=$models.Lead legend="Lead Details"}
			<dl id="view_data_left">
				{input type='hidden'  attribute='id' }
				{input type='hidden' attribute='is_lead' value='on'}
				{input type='hidden'  attribute='party_id' }
				{include file='elements/auditfields.tpl' }
				{view_section heading="account_details"}
					{input type='text'  attribute='name' class="compulsory" }
					{select attribute='parent_id' ignore_parent_rel=true label='Parent Account'}
					{select attribute="assigned"}
					{select attribute="owner"}
				{/view_section}
				{view_section heading="organisation_details"}
						{input type='text'  attribute='vatnumber' }
						{input type='text'  attribute='companynumber' }
						{input type='text'  attribute='employees' }
				{/view_section}
				{if $crm_access}
					{view_section heading="crm_details"}
						{with model=$Lead}
							{input type="hidden" attribute='id'}
							{select attribute="classification_id"}
							{select attribute="rating_id"}
							{select attribute="industry_id"}
							{select attribute="source_id"}
							{select attribute="status_id"}
							{select attribute="type_id"}
						{/with}
					{/view_section}
				{/if}
			</dl>
			<dl id="view_data_right">
				{view_section heading="contact_details"}
					{with group='phone'}
						{with model=$Lead->phone->contactmethod}
							{input type='hidden' attribute='id'}
							{input type='text' attribute="contact" label="phone"}
						{/with}
						{with model=$Lead->phone}
							{input type='hidden' attribute='id'}
							{input type='hidden' attribute='contactmethod_id'}
							{input type='hidden' attribute='party_id'}
							{input type='hidden' attribute='type' value='T'}
							{input type='hidden' attribute='name' value='MAIN'}
							{input type='hidden' attribute='main' value='t'}
							{input type='hidden' attribute='billing' value='t'}
							{input type='hidden' attribute='shipping' value='t'}
							{input type='hidden' attribute='payment' value='t'}
							{input type='hidden' attribute='technical' value='t'}
						{/with}
					{/with}
					{with group='fax'}
						{with model=$Lead->fax->contactmethod}
							{input type='hidden' attribute='id'}
							{input type='text' attribute="contact" label="fax"}
						{/with}
						{with model=$Lead->fax}
							{input type='hidden' attribute='id'}
							{input type='hidden' attribute='contactmethod_id'}
							{input type='hidden' attribute='party_id'}
							{input type='hidden' attribute='type' value='F'}
							{input type='hidden' attribute='name' value='MAIN'}
							{input type='hidden' attribute='main' value='t'}
							{input type='hidden' attribute='billing' value='t'}
							{input type='hidden' attribute='shipping' value='t'}
							{input type='hidden' attribute='payment' value='t'}
							{input type='hidden' attribute='technical' value='t'}
						{/with}
					{/with}
					{with group='email'}
						{with model=$Lead->email->contactmethod}
							{input type='hidden' attribute='id'}
							{input type='text' attribute="contact" label="email"}
						{/with}
						{with model=$Lead->email}
							{input type='hidden' attribute='id'}
							{input type='hidden' attribute='contactmethod_id'}
							{input type='hidden' attribute='party_id'}
							{input type='hidden' attribute='type' value='E'}
							{input type='hidden' attribute='name' value='MAIN'}
							{input type='hidden' attribute='main' value='t'}
							{input type='hidden' attribute='billing' value='t'}
							{input type='hidden' attribute='shipping' value='t'}
							{input type='hidden' attribute='payment' value='t'}
							{input type='hidden' attribute='technical' value='t'}
						{/with}
					{/with}
					{with model=$Lead}
						{input type='text' attribute="website"}
					{/with}
				{/view_section}
				{view_section heading="address_details"}
					{with model=$Lead->main_address->address}
						{input type='hidden' attribute='id' }
						{input type='text' attribute='street1' }
						{input type='text' attribute='street2' }
						{input type='text' attribute='street3' }
						{input type='text' attribute='town' }
						{input type='text' attribute='county' }
						{input type='text' attribute='postcode' }
						{select attribute='countrycode' }
					{/with}
					{with model=$Lead->main_address}
						{input type='hidden' attribute='id' }
						{input type='hidden' attribute='address_id' }
						{input type='hidden' attribute='party_id' }
						{input type='hidden' attribute='name' value='MAIN' }
						{input type='hidden' attribute='main' value='t'}
						{input type='hidden' attribute='billing' value='t'}
						{input type='hidden' attribute='shipping' value='t'}
						{input type='hidden' attribute='payment' value='t'}
						{input type='hidden' attribute='technical' value='t'}
					{/with}
				{/view_section}
				{view_section heading="categories"}
					<dt>
						<label for="category_id">
							{"categories"|prettify}
						</label>
					</dt>
					<dd class="for_textarea">
						<select name="ContactCategories[category_id][]" multiple="multiple" size="7">
							{html_options options=$contact_categories selected=$selected_categories}
						</select>
					</dd>
				{/view_section}
				{view_section heading=""}
					{submit}
				{/view_section}
			</dl>
		{/with}
	{/form}
	<div id="view_page" class="clearfix">
		<dl id="view_data_right">
			{include file="elements/cancelForm.tpl"}
		</dl>
	</div>
{/content_wrapper}