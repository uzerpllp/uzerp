{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.13 $ *}
{content_wrapper}
	{form controller="periodicpayments" action="save"}
		{with model=$models.PeriodicPayment legend="PeriodicPayment Details"}
			<dl id="view_data_left">
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{if $action=='edit'}
					{select attribute='status' class="compulsory" }
					{view_data attribute='source' id='source_text'}
					{input type='hidden' attribute="source"}
					{view_data attribute='company'}
					{input type='hidden' attribute="company_id"}
				{else}
					{select attribute='source' class="compulsory" }
					{select attribute="company_id" label='Company' options=$companies nonone=true}
				{/if}
				<div id="pp_person">
					{select attribute='person_id' class="compulsory" options=$people }
				</div>
				{select attribute='cb_account_id' class="compulsory" label="Bank Account" value=$cb_account_id}
				{select attribute='currency_id' class="compulsory" value=$currency}
				{select attribute='payment_type_id' class="compulsory" }
				{select attribute='tax_rate_id' class="compulsory" }
				{select attribute='frequency' class="compulsory" }
				<div id="pp_account_centre">
					{select attribute='glaccount_id' label='Account' options=$gl_accounts nonone=true}
					{select attribute='glcentre_id' label='Centre' options=$gl_centres nonone=true}
				</div>
			</dl>
			<dl id="view_data_right">
				{input type="text" attribute="description" }
				{input type="text" attribute="ext_reference" }
				{input type="date" attribute="start_date" }
				{if $action=='edit'}
					{input type="date" attribute="next_due_date" }
				{/if}
				{input type="date" attribute="end_date" }
				{input type="text" attribute="occurs" class="numeric"}
				{if $action=='edit'}
					{view_data attribute="current" label='Payments Made'}
				{/if}
				<div id="pp_net_tax">
					{input type="text" attribute="net_value" class="numeric" }
					{input type="text" attribute="tax_value" class="numeric" }
				</div>
				{input type="text" attribute="gross_value" class="numeric" }
				{input type="checkbox" attribute="variable" }
				{input type="checkbox" attribute="write_variance" }
			</dl>
		{/with}
		<dl id="view_data_bottom">
			{submit tags='none'}
			{include file='elements/saveAnother.tpl' tags='none'}
		</dl>
	{/form}
	{include file='elements/cancelForm.tpl' tags='none'}
{/content_wrapper}