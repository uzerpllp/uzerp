{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.23 $ *}
{content_wrapper}
	<dl class="float-left">
		{form controller=$controller action="save_payment"}
			{with model=$CBTransaction}
				{input type="hidden" attribute="source" value="$source"}
				{input type="hidden" attribute="transaction_type" value="$type"}
			{/with}
			{with model=$Transaction}
				{assign var=model_name value=$Transaction->get_name()}
				{select attribute=$master_id nonone=true options=$companies value=$master_value label="$master_label" force=true}
				{input type="hidden" attribute=company_id value=$company_id}
				{if $master_id=='slmaster_id'}
					{select attribute="person_id" label='Person' options=$people}
				{/if}
			{/with}
			{with model=$CBTransaction}
				{select attribute="cb_account_id" label="Bank Account" value=$bank_account options=$bank_accounts}
				{input type="date" attribute="transaction_date"}
				{input type="text" attribute="ext_reference"}
				{input type="text" attribute="description"}
				{select attribute="payment_type_id" value=$payment_type}
				{input type="hidden" attribute="currency_id"}
				{view_data attribute="currency" value=$currency}
				{input type="text" attribute="net_value" label="Value"}
				<div id='conversion_rate'>
					{input type="text" attribute="rate" label="Conversion Rate" value=$rate}
				</div>
			{/with}
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/form}
	</dl>
	<script type="text/javascript" >
	
		// as this is a generic page, we're going to set the rule here... mainly as we need to target the smarty vars passed
		// could probably pass the key vars through as a javascript function to set this rule up elsewhere
		// some of these dont even seem generic themselves?
		
		$(document).ready(function(){
		
			var target_elements = [{
							element	: "#CBTransaction_cb_account_id",
							field	: "cb_account_id"
						},
						{
							element	: "#CBTransaction_currency_id",
							field	: "currency_id"
						},
						{
							element	: "#CBTransaction_currency",
							field	: "currency"
						},
						{
							element	: "#CBTransaction_payment_type_id",
							action	: "selected",
							field	: "payment_type_id"
						},
						{
							element	: "#{$model_name}_company_id",
							field	: "company_id"
						}];
						
			if ('{$model_name}'=='SLTransaction') {
			// Currently, only SL has person
				$.merge(target_elements, [{
											element	: "#{$model_name}_person_id",
											field	: "person_id"
										   }]);
			}
			
		 	$("#{$model_name}_{$master_id}").change(function() {
				$.uz_ajax({
					target: target_elements,
					data:{
						module		: '{$module}',
						controller	: '{$controller}',
						action		: 'getCustomerData',
						id			: $(this).val(),
						fields		: "currency_id,currency,payment_type_id,company_id",
						ajax		: ''
					}
				});
				
			});
			
		 	$("#CBTransaction_cb_account_id").change(function() {
				$("#CBTransaction_rate").uz_ajax({
					data:{
						module			: '{$module}',
						controller		: '{$controller}',
						action			: 'getAccountRate',
						id				: $("#{$model_name}_{$master_id}").val(),
						cb_account_id	: $(this).val(),
						ajax			: ''
					}
				});
				
			});
			
			$("#CBTransaction_net_value").change(function() {
				$(this).val(roundNumber($(this).val(), 2));
			});
			
			$("#CBTransaction_rate").change(function() {
				if ($(this).val()=='') {
					$("#conversion_rate").hide();
				}
				else {
					$("#conversion_rate").show();
				}
			});
			
			if ($("#CBTransaction_rate").val()=='') {
				$("#conversion_rate").hide();
			}
			else {
				$("#conversion_rate").show();
			}
		});
		
	</script>
{/content_wrapper}