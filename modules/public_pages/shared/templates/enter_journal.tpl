{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.21 $ *}
{content_wrapper}
	{assign var=model_name value=$Transaction->get_name()}
	<dl class="float-left">
		{form controller=$controller action="save_journal"}
			{with model=$Transaction}
				{input type="hidden" attribute="source" value=$source}
				{input type="hidden" attribute="transaction_type" value="J"}
				{select attribute=$master_id nonone=true options=$companies value=$master_value label="$master_label" force=true}
				{input type="hidden" attribute=company_id value=$company_id}
				{select attribute="person_id" label='Person' options=$people}
				{input type="date" attribute="transaction_date"}
				{input type="text" attribute="ext_reference"}
				{input type="text" attribute="comment" label="Description"}
				{select attribute='glaccount_id' options=$gl_accounts force=true label='Account *' nonone=true class='required'}
				{select attribute='glcentre_id' options=$centres label='Centre *' nonone=true class='required'}
				{input type="text" attribute="net_value" label="Value"}
			{/with}
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/form}
	</dl>
	<script type="text/javascript">
		// as this is a generic page, we're going to set the rule here... mainly as we need to target the smarty vars passed
		// could probably pass the key vars through as a javascript function to set this rule up elsewhere
		$(document).ready(function(){

			if ('{$model_name}'=='SLTransaction') {
			// Currently, only SL has person
		 		$("#{$model_name}_{$master_id}").change(function() {
					$.uz_ajax({
						target: [{
									element	: "#{$model_name}_person_id",
									field	: "person_id"
								},
								{
									element	: "#{$model_name}_company_id",
									field	: "company_id"
								}],
						data:{
							module		: '{$module}',
							controller	: '{$controller}',
							action		: 'getCustomerData',
							id			: $(this).val(),
							fields		: "company_id",
							ajax		: ''
						}
					});
				});
			}
			
		 	$("#{$model_name}_glaccount_id").change(function() {
				$('#{$model_name}_glcentre_id').uz_ajax({
					data:{
						module		: '{$module}',
						controller	: '{$controller}',
						action		: 'getCentres',
						id			: $(this).val(),
						ajax		: ''
					}
				});
			});
		});
	</script>
{/content_wrapper}