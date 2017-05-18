{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.33 $ *}	
{content_wrapper class="clearfix uz-grid" }
	{with model=$models.POrder legend="POrder Details"}
		<div id="view_page" class="clearfix">
			{form controller="porders" action="save" notags=true}
			    <dl class="float-left" >
					{input type='hidden' attribute='id' }
					{input type='hidden' attribute='type' value=$model->type}
					<input type='hidden' name='trans_type' value={$trans_type}>
					<input type='hidden' id='default_receive_action' name='default_receive_action' >
					{include file='elements/auditfields.tpl' }
					{if $model->isLoaded()}
						<b>{view_data attribute="order_number" label='number'}</b>
					{/if}
					{if $model->net_value==0}
						{select attribute="plmaster_id" label='Supplier' force=true value=$selected_supplier use_collection=true}
					{else}
						{input type='hidden' attribute="plmaster_id"}
						{view_data attribute="supplier" label='Supplier'}
					{/if}
					<dt>Supplier Ref</dt>
					<dd>{input type='text'  attribute='ext_reference' label=' ' tags='none'}</dd>
					{select attribute=receive_action options=$receive_actions value=$default_receive_action label='Receive Into' nonone=true}
					{select attribute='delivery_term_id' value=$customer_term}
				</dl>
			    <dl class="float-right" >
					<dt>Order Date</dt>
					<dd>{input type='date'  attribute='order_date' label=' ' tags='none'}</dd>
					<dt>Due Date</dt>
					<dd>{input type='date'  attribute='due_date' label=' ' tags='none'}</dd>
					{select attribute=owner}
					{select attribute='sales_order_id' options=$sales_orders force=true}
					{input type='checkbox' attribute='use_sorder_delivery' label='Use sales order delivery address'}
					{select attribute='project_id' force=true}
					{select attribute='task_id' options=$tasks force=true}
				</dl>
				<dl class="view_data_bottom">
					{textarea  attribute='description' label='Description' }
					{submit}
				</dl>
			{/form}
			<dl class="view_data_bottom">
				{include file="elements/cancelForm.tpl"}
				{view_section heading="Notes"}
					<dt><label for="notes"></label></dt>
					<dd class="inline" id="notes">
						{include file="elements/datatable_inline.tpl"}
					</dd>
				{/view_section}
			</dl>
		</div>
	{/with}
{/content_wrapper}