{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	<dl id="view_data_left">
		{form controller="bankaccounts" action="save"}
			{with model=$models.CBAccount legend="BankAccount Details"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{input attribute="name"}
				{input type='checkbox' attribute="primary_account"}
				{input type='text'  attribute='description' class="compulsory" }
				{input type='text'  attribute='bank_account_name' class="compulsory" }
				{input attribute="bank_name"}
				{input type='text'  attribute='bank_sort_code' class="compulsory" }
				{input type='text'  attribute='bank_account_number' class="compulsory" }
				{input type='text'  attribute='bank_address' class="compulsory" }
				{input type='text'  attribute='bank_iban_number' class="compulsory" }
				{input type='text'  attribute='bank_bic_code' class="compulsory" }
				{select attribute='currency_id' value=$currency}
				{select attribute='glaccount_id' force=true nonone=true label='GL Account' options=$glaccounts}
				{select attribute='glcentre_id' force=true nonone=true label='Cost Centre' options=$glcentres}
				{input type='text'  attribute='statement_balance' class="compulsory" }
				{input type='text'  attribute='statement_page' class="compulsory" }
				{input type='text'  attribute='balance' class="compulsory" }
			{/with}
			{submit}
		{/form}
		{include file='elements/cancelForm.tpl' action='index'}
	</dl>
{/content_wrapper}