{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.57 $ *}
{content_wrapper class="clearfix uz-grid" }
	{with model=$models.SOrder legend="SOrder Details"}
		<div id="view_page" class="clearfix">
			{form controller="sorders" action="save" notags=true}
			    <dl class="float-left" >
					{input type='hidden'  attribute='id' }
					{include file='elements/auditfields.tpl' }
					{if !$model->isLoaded()}
						{select attribute='type' }
					{/if}
					{if $model->isLoaded()}
						<b>{view_data attribute="order_number" label='number'}</b>
						{input type='hidden'  attribute='type' }
					{/if}
					{input type="hidden" attribute="company_id" value=$company_id}
					<input type="hidden" id="input_person_id" value="{$model->person_id}" />
					<input type="hidden" id="input_del_address_id" value="{$model->del_address_id}" />
					<input type="hidden" id="input_inv_address_id" value="{$model->inv_address_id}" />
					<input type="hidden" id="company_type" value="company" />
					{if $model->net_value==0}
						{select attribute='slmaster_id' label='Customer' force=true value=$selected_customer use_collection=true} 
					{else}
						{input type='hidden' attribute="slmaster_id"}
						{view_data attribute="customer" label='Customer'}
					{/if}
					<dt>Customer Ref</dt>
					<dd>{input type='text'  attribute='ext_reference' label=' ' tags='none'}</dd>
					{select attribute='despatch_action' label='Despatch From' nonone=true options=$despatch_actions value=$default_despatch_action}
					<input type="hidden" id="billing_type" value="billing" />
					<input type="hidden" id="shipping_type" value="shipping" />
					<input type='hidden' id='person_type' value='person' />
					{input type="hidden" attribute="default_inv_address_id" value=$default_inv_address }
					{select label='For Attn: of' attribute='person_id' nonone=true force=true depends="slmaster_id"}
					{select attribute='del_address_id' options=$deliveryAddresses nonone=true label='Delivery Address: *'}
					{select attribute='inv_address_id' options=$invoiceAddresses value=$invoice_address nonone=true label='Invoice Address'}
					{select attribute='delivery_term_id' value=$customer_term}
				</dl>
			    <dl class="float-right" >
					<dt>Order Date</dt>
					<dd>{input type='date'  attribute='order_date' label=' ' tags='none'}</dd>
					<dt>Due Date</dt>
					<dd>{input type='date'  attribute='due_date' label=' ' tags='none'}</dd>
					<dt>Despatch Date</dt>
					<dd>{input type='date'  attribute='despatch_date' label=' ' tags='none'}</dd>
					<div id="payment_terms">
						{include file="./payment_terms.tpl"}
					</div>
					{select attribute='project_id' force=true}
					{select attribute='task_id' options=$tasks force=true}
					{input type="text" attribute="text1"}
					{input type="text" attribute="text2"}
					{input type="text" attribute="text3"}
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