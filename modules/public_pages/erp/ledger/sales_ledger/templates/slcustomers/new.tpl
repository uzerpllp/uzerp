{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.20 $ *}
{content_wrapper}
	<dl id="view_data_left">
		{form controller="slcustomers" action="save"}
			{with model=$models.SLCustomer legend="SLCustomer Details"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{if !isset($company_list)}
					<h3>
						{if $model->isLoaded()}
							Change
						{else}
							Add
						{/if}
						details for {$model->name}
					</h3>
					{view_data attribute='name'}
					{input type='hidden' attribute='company_id'}
				{else}
					<h3>Add a New Customer</h3>
					{select attribute="company_id" options=$company_list label='Customer'}
				{/if}
				{input type='checkbox' attribute='statement' }
				{select attribute='billing_address_id' options=$billing_addresses}
				{if $action=='new' || $transaction_count==0}
					{select attribute='currency_id'}
				{/if}
				{select attribute='cb_account_id' label='Bank Account' options=$bank_accounts value=$bank_account}
				{input attribute='credit_limit' }
				{select attribute='payment_term_id' }
				{select attribute='payment_type_id' }
				{select attribute='so_price_type_id' }
				{if $action=='new' }
					{select attribute='tax_status_id' }
				{else}
					{if $transaction_count == 0}
						{select attribute='tax_status_id' }
					{/if}
				{/if}
				{select attribute='delivery_term_id' }	
				{select attribute='despatch_action' label='Despatch From' options=$despatch_actions}
				{select attribute='sl_analysis_id' }
				{select attribute='invoice_method' }
				{if ($module_prefs['sales-invoice-report-type'] !== '')}
					{select attribute='report_def_id' label='Invoice Print Layout' options=$invoice_layouts nonone=true default='Default' nonew=true}
				{/if}
				{select attribute='email_invoice_id' options=$emails}
				{select attribute='email_statement_id' options=$emails}
				{select attribute='edi_invoice_definition_id'}
			{/with}
			{submit}
		{/form}
		{include file='elements/cancelForm.tpl' action='index'}
	</dl>
{/content_wrapper}