{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	{form controller=$self.controller action="savepayment"}
		<dl class="float-left">
		{with model=$CBTransaction}
			<h3>Make a Payment<br>
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
			{select attribute="currency_id" options=$currencies value=$currency_id}
			{input type="text" attribute="net_value"}
			<div id='conversion_rate'>
				{input type="text" attribute="rate" label="Conversion Rate" value=$rate}
			</div>
			{select attribute='person_id' label='Employee' data=$employees nonone='true'}
			{submit}
			</dl>
		{/with}
	{/form}
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