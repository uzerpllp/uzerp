{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.19 $ *}
{content_wrapper}
	<dl id="view_data_left">
		{form controller="plsuppliers" action="save"}
			{with model=$models.PLSupplier legend="PLCustomer Details"}
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
					<h3>Add a New Supplier</h3>
					{select attribute="company_id" class="required" options=$company_list label='Supplier'}
				{/if}
				{input attribute='payee_name' value=$payee}
				{input type='checkbox'  attribute='remittance_advice' }
				{select attribute='payment_address_id' options=$payment_addresses}
				{if $action=='new' || $transaction_count == 0}
					{select attribute='currency_id' }
				{/if}
				{select attribute='cb_account_id' label='Bank Account'}
				{input attribute='credit_limit' }
				{select attribute='payment_term_id' }
				{select attribute='payment_type_id' }	
				{if $action=='new' }
					{select attribute='tax_status_id' }
				{else}
					{if $transaction_count == 0}
						{select attribute='tax_status_id' }
					{/if}
				{/if}
				{select attribute='delivery_term_id' }	
				{select attribute='receive_action' label='Receive Into' options=$receive_actions}
				{select attribute='order_method' }
				{select attribute='email_order_id' options=$emails}
				{select attribute='email_remittance_id' options=$emails}
				{input type='text' attribute='sort_code' class="compulsory" }
				{input type='text' attribute='account_number' class="compulsory" }
				{input type='text' attribute='bank_name_address' class="compulsory" }
				{input type='text' attribute='iban_number' class="compulsory" }
				{input type='text' attribute='bic_code' class="compulsory" }
			{/with}
			{submit}
		{/form}
		{include file='elements/cancelForm.tpl' action='index'}
	</dl>
{/content_wrapper}