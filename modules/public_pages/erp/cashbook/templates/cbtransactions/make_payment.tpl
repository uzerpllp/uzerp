{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.19 $ *}
{content_wrapper}
	{form controller=$self.controller action="save"}
		{with model=$CBTransaction}
			{select attribute="cb_account_id" label='Account' force=true value=$account_id}
			{select attribute='currency_id' value=$currency_id options=$currencies}
			{input type="hidden" attribute="type" value="P"}
			{input type="text" attribute="net_value" }
			{select attribute="tax_rate_id"}
			{input attribute="tax_value" }
			{input attribute="gross_value" readonly=true}
			<div id='conversion_rate'>
				{input type="text" attribute="rate" label="Conversion Rate" value=$rate}
			</div>
			{input type="date" attribute="transaction_date"}
			{select attribute="company_id" label='Company' constrains='person_id'}
			{select attribute="person_id" label='Person' depends='company_id'}
			{input type="text" attribute="ext_reference"}
			{input type="text" attribute="description"}
			{select attribute="payment_type_id"}
			{select attribute='glaccount_id' options=$gl_accounts nonone=true force=true label='Account *' class="required"}
			{select attribute='glcentre_id' options=$gl_centres nonone=true label='Centre *' class="required"}
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