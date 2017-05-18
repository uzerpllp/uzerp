{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	<dl class="float-left">
		{form controller=$self.controller action="savepayment"}
			{input type="hidden" attribute='id' model=$Expense}
			{with model=$CBTransaction}
				<h3>Pay Claim<br>
					{if $account<>''}
						from {$account}<br>
					{/if}
				</h3>
				{select attribute="cb_account_id" label='Account' options=$accounts value=$account_id}
				{input type="hidden" attribute='company_id'}
				{input type="hidden" attribute="transaction_type" value="P"}
				{input type="date" attribute="transaction_date"}
				{input type="text" attribute="ext_reference"}
				{input type="text" attribute="description"}
				{select attribute="payment_type_id"}
				{input type="hidden" attribute="currency_id" value=$Expense->currency_id}
				{view_data attribute="currency_id"}
				{input type="hidden" attribute="net_value" value=$Expense->gross_value}
				{view_data attribute="net_value"}
				<div id='conversion_rate'>
					{input type="text" attribute="rate" label="Conversion Rate" value=$rate}
				</div>
				{input type="hidden" attribute='employee_id' value=$Expense->employee_id}
				{input type="hidden" attribute='person_id' value=$CBTransaction->person_id}
				{view_data attribute='person' value=$person}
				{submit}
			{/with}
		{/form}
		{include file='elements/cancelForm.tpl'}
	</dl>
	<script type="text/javascript" >
	
		$(document).ready(function(){
		
			if ($("#CBTransaction_rate").val()=='') {
				$("#conversion_rate").hide();
			}
			else {
				$("#conversion_rate").show();
			}
		});
		
	</script>
{/content_wrapper}