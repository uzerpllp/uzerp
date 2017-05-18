{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	{form controller=$self.controller action="savemovement"}
		{with model=$CBTransaction}
			{input type="hidden" attribute="type" value="T"}
			{select attribute="cb_account_id" options=$accounts label='From Account' force=true value=$account_id}
			{select attribute="to_account_id" options=$to_accounts nonone=true force=true label='To Account *' class='required'}
			{select attribute="payment_type_id"}
			{select attribute="currency_id" options=$currencies value=$currency_id}
			{input type="text" attribute="net_value"}
			<div id='conversion_rate'>
				{input type="text" attribute="rate" label="Conversion Rate" value=$rate}
			</div>
			{input type="date" attribute="transaction_date"}
			{input type="text" attribute="ext_reference"}
			{input type="text" attribute="description"}
			{submit}
			{include file='elements/saveAnother.tpl'}
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