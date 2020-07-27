{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{* 	$Revision: 1.19 $ *}
{content_wrapper}
	{form controller="companys" action="save"}
		{with model=$Company->party}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='type' value='Company'}
		{/with}
		{with model=$models.Company legend="Company Details"}
			<div id="view_page" class="clearfix">
				<dl id="view_data_left">
					{input type='hidden'  attribute='id' }
					{input type='hidden'  attribute='party_id' }
					{include file='elements/auditfields.tpl' }
					{view_section heading="account_details"}
						{input type='text'  attribute='name' class="compulsory" }
						{input type='text'  attribute='accountnumber' class="compulsory" }
						{select attribute="parent_id" label='Parent Company'}
						{select attribute="assigned"}
						{select attribute="owner"}
						{if !$model->isSystemCompany()}
						{input type='date' attribute='date_inactive'}
						{/if}
					{/view_section}
					{view_section heading="organisation_details"}
						{input type='text'  attribute='tax_description' }
						{input type='text'  attribute='vatnumber' label=$model->tax_description}
						{input type='text'  attribute='companynumber' }
						{input type='text'  attribute='employees' }
					{/view_section}
					{if $crm_access}
						{view_section heading="crm_details"}
							{input type="hidden" attribute='id'}
							{select attribute="classification_id"}
							{select attribute="rating_id"}
							{select attribute="industry_id"}
							{select attribute="source_id"}
							{select attribute="status_id"}
							{select attribute="type_id"}
						{/view_section}
					{/if}
					{view_section heading="Additional"}
							{input type='text'  attribute='text1' label='Text 1'}
							{input type='text'  attribute='text2' label='Text 2'}
					{/view_section}
				</dl>
				<dl id="view_data_right">
					{view_section heading="contact_details"}
						{with group='phone'}
							{with model=$Company->phone->contactmethod}
								{input type='hidden' attribute='id'}
								{input type='text' attribute="contact" label="phone"}
							{/with}
							{with model=$Company->phone}
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
							{with model=$Company->fax->contactmethod}
								{input type='hidden' attribute='id'}
								{input type='text' attribute="contact" label="fax"}
							{/with}
							{with model=$Company->fax}
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
							{with model=$Company->email->contactmethod}
								{input type='hidden' attribute='id'}
								{input type='text' attribute="contact" label="email"}
							{/with}
							{with model=$Company->email}
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
						{with model=$Company}
							{input type='text' attribute="website"}
						{/with}
					{/view_section}
					{view_section heading="address_details"}
						{with model=$Company->main_address->address}
							{input type='hidden' attribute='id' }
							{input type='text' attribute='street1' }
							{input type='text' attribute='street2' }
							{input type='text' attribute='street3' }
							{input type='text' attribute='town' }
							{input type='text' attribute='county' }
							{input type='text' attribute='postcode' }
							{select attribute='countrycode' }
						{/with}
						{with model=$Company->main_address}
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
				</dl>
			</div>
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}