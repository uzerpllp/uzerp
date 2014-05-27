{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.14 $ *}	
{content_wrapper}
	{form controller=$controller|default:"persons" action="save"}
		{include file='elements/auditfields.tpl' }
		{with model=$Person->party}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='type' value='Person'}
		{/with}
		{with model=$models.Person legend="Person Details"}
			<dl class="float-left">
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='party_id' }
				{include file='elements/auditfields.tpl' }
				{view_section heading="person_details"}
					{input type='text'  attribute='title' }
					{input type='text'  attribute='firstname' class="compulsory" }
					{input type='text'  attribute='middlename' }
					{input type='text'  attribute='surname' class="compulsory" }
					{input type='text'  attribute='suffix' }
					{if $company!=''}
						{view_data attribute=company value=$company}
					{/if}
					{select attribute='company_id' nonone=true constrains='reports_to'} {* cascades='reports_to' *}
					{select attribute='assigned_to' }
					{input type='date' attribute='end_date' }
				{/view_section}
				{view_section heading="job_details"}
					{input type='text'  attribute='jobtitle' }
					{input type='text'  attribute='department' }
					{select attribute='reports_to' label='Line Manager' depends='company_id' ignore_parent_rel=true}
					{select  attribute='lang' label='Language' class="compulsory" }
				{/view_section}
				{if "categories"|prettify <> 'EGS_HIDDEN_FIELD'}
						{view_section heading="categories"}
							<dt>
								<label for="category_id">
								</label>
							</dt>
							<dd class="for_textarea">
								<select name="ContactCategories[category_id][]" multiple="multiple" size="7">
									{html_options options=$contact_categories selected=$selected_categories}
								</select>
							</dd>
						{/view_section}
				{/if}
			</dl>
			<dl class="float-right">
				{view_section heading="contact_details"}
					{with group='phone'}
						{with model=$phone}
							{input type='hidden' attribute='id'}
							{input type='text' attribute="contact" label="phone"}
						{/with}
						{with model=$Person->phone}
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
					{with group='mobile'}
						{with model=$mobile}
							{input type='hidden' attribute='id'}
							{input type='text' attribute="contact" label="mobile"}
						{/with}
						{with model=$Person->mobile}
							{input type='hidden' attribute='id'}
							{input type='hidden' attribute='contactmethod_id'}
							{input type='hidden' attribute='party_id'}
							{input type='hidden' attribute='type' value='M'}
							{input type='hidden' attribute='name' value='MAIN'}
							{input type='hidden' attribute='main' value='t'}
							{input type='hidden' attribute='billing' value='t'}
							{input type='hidden' attribute='shipping' value='t'}
							{input type='hidden' attribute='payment' value='t'}
							{input type='hidden' attribute='technical' value='t'}
						{/with}
					{/with}
					{with group='fax'}
						{with model=$fax}
							{input type='hidden' attribute='id'}
							{input type='text' attribute="contact" label="fax"}
						{/with}
						{with model=$Person->fax}
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
						{with model=$email}
							{input type='hidden' attribute='id'}
							{input type='text' attribute="contact" label="email"}
						{/with}
						{with model=$Person->email}
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
				{/view_section}
				{view_section heading="address_details"}
					{select model=$address attribute='fulladdress' nonone=true options=$addresses value=$address->id label='Select Address'}
					{if $address->id==''}
						<div id='address'>
					{else}
						<div id='address' style="display: none;">
					{/if}
						{include file='elements/address.tpl' }
					</div>
					{with model=$Person->main_address}
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
				{view_section heading="contact_criteria"}
					{with model=$Person}
						{input type='checkbox' label='Available to call' attribute='can_call' }
						{input type='checkbox' label='Available to email' attribute='can_email' }
					{/with}
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