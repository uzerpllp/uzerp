{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.45 $ *}
{content_wrapper class="clearfix uz-grid" }
	{with model=$models.SInvoice legend="SInvoice Details"}
		<div id="view_page" class="clearfix">
			{form controller="sinvoices" action="save" notags=true}
			    <dl class="float-left" >
					{view_section heading="Details"}
					{input type='hidden'  attribute='id'}
					{include file='elements/auditfields.tpl' }
					{if $action == 'edit'}
						{view_data attribute="invoice_number" label=$SInvoice->getFormatted('transaction_type')|cat:' number'}
						{view_data attribute="net_value"}
					{/if}
					{input type='text'  attribute='sales_order_number'}
					{input type='date' label="$transaction_type_desc Date" attribute='invoice_date'}
					{select attribute='transaction_type'}
					{input type='hidden' attribute="company_id" value=$company_id}
					{if $model->net_value==0}
						{select attribute='slmaster_id' label='Customer' force=true value=$selected_customer use_collection=true} 
					{else}
						{input type='hidden' attribute="slmaster_id"}
						{view_data attribute="customer" label='Customer'}
					{/if}
					{input type='text'  attribute='ext_reference' label='Customer Ref'}
					<input type="hidden" id="input_person_id" value="{$model->person_id}" />
					<input type="hidden" id="input_del_address_id" value="{$model->del_address_id}" />
					<input type="hidden" id="input_inv_address_id" value="{$model->inv_address_id}" />
					<input type="hidden" id="shipping_type" value="shipping" />
					<input type="hidden" id="billing_type" value="billing" />
					{input type="hidden" attribute="default_inv_address_id" value=$default_inv_address }
					{select label='For Attn: of' attribute='person_id' nonone=true depends="slmaster_id"}
					{select attribute='del_address_id' label='Delivery Address' nonone=true options=$deliveryAddresses}
					{select attribute='inv_address_id' label='Invoice Address' options=$invoiceAddresses value=$invoice_address}
					{/view_section}
					{submit}
				</dl>
			    <dl class="float-right" >
					{view_section heading="Description"}
						{textarea  attribute='description' tags=none label=' '}
					{/view_section}
				</dl>
			{/form}
		</div>
		<div id="view_page" class="clearfix">
			<dl class="float-left">
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