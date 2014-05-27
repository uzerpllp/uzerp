{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.12 $ *}
{content_wrapper class="clearfix uz-grid" }
	<dl id="view_data_left">
		{form controller="porderlines" action="save"}
			{with model=$porder legend="Purchase Order Header"}
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='plmaster_id' }
				{include file='elements/auditfields.tpl' }
			{/with}
			{with model=$models.POrderLine legend="Purchase Order Line Details"}
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='order_id' }
				{input type="hidden" attribute="status"}
				{include file='elements/auditfields.tpl' }
				{input type="hidden" attribute="line_number"}
				{if $model->line_number != '' }
					{view_data attribute="line_number"}
				{/if}
				{view_data attribute="status"}
				{if $model->status==$model->newStatus()}
					{if isset($display_fields.product_search)}
						<input type='hidden' id=prod_search_limit name=limit value={$config.AUTOCOMPLETE_SELECT_LIMIT}>
					{else}
						<input type='hidden' id=prod_search_limit name=limit value=0>
					{/if}
					{* The following is a hack to prevent date picker showing on dialog open *}
					<span class="ui-helper-hidden-accessible"><input type="text"/></span>
					{input type='date' attribute="due_delivery_date"}
					{if isset($display_fields.product_search)}
						{input type='text' attribute="product_search" value=$product_search}
					{/if}
					{select attribute="productline_id" label='product' options=$productline_options nonone=true}
					{input type="text" attribute="description" }
					{input type="text" attribute="revised_qty" label="Quantity" class="order_qty numeric" }
					{if $model->id!=''}
						{view_data attribute="order_qty" label="Original Quantity"}
					{/if}
					{input type="text" attribute="price" class="price numeric" size="10" }
					{select attribute="stuom_id" class="short_select" options=$stuom_options}
					{select attribute="glaccount_id" options=$glaccount_options }
					{select attribute="glcentre_id" options=$glcentre_options}
					{select attribute="tax_rate_id" options=$taxrate_options class='tax_rate'}
					{input type="text" attribute="net_value" class="net_value numeric" size="10" readonly=true }
				{else}
					{input type="hidden" attribute="productline_id" }
					{input type="hidden" attribute="description" }
					{input type="hidden" attribute="order_qty" }
					{input type="hidden" attribute="del_qty" }
					{input type="hidden" attribute="price" }
					{input type="hidden" attribute="stuom_id" }
					{input type="hidden" attribute="tax_rate_id" }
					{input type="hidden" attribute="net_value" }
					{if $model->status!=$model->receivedStatus()}
						{input type='date' attribute="due_delivery_date"}
					{else}
						{view_data attribute="due_delivery_date"}
						{input type="hidden" attribute="due_delivery_date" }
					{/if}
					{view_data attribute="product_description"}
					{view_data attribute="description"}
					{if $model->status!=$model->receivedStatus() && $amend_qty}
						{input type="text" attribute="revised_qty"  label="Quantity" class="order_qty numeric" }
					{else}
						{view_data attribute="revised_qty" label="Quantity"}
						{input type="hidden" attribute="revised_qty" }
					{/if}
					{view_data attribute="order_qty" label="Original Quantity"}
					{view_data attribute="del_qty" label="Delivered Quantity"}
					{view_data attribute="os_qty" label="Outstanding Quantity"}
					{view_data attribute="price"}
					{view_data attribute="uom_name"}
					{select attribute="glaccount_id" options=$glaccount_options }
					{select attribute="glcentre_id" options=$glcentre_options}
					{view_data attribute="tax_rate_id"}
					{view_data attribute="net_value"}
				{/if}
				{if $model->id>0}
					{if $model->status==$model->newStatus() ||  $model->status==$model->lineAwaitingDelivery()}
						{input type="checkbox" attribute="cancel_line" class="checkbox" label='Cancel Line'}
					{elseif $model->status==$model->partReceivedStatus()}
						{input type="checkbox" attribute="complete_line" class="checkbox" label='Complete Line'}
					{/if}
				{/if}
			{/with}
			{if !$dialog}
				{submit}
				{submit name="saveAnother" value="Save and Add Another"}
			{/if}
		{/form}
		{if !$dialog}
			{with model=$models.POrderLine  legend="Purchase Order Line Details"}
				{if $model->id!=''}
					{form id='delete_form' controller="porderlines" action="delete"}
						{input type='hidden' attribute='id' }
						{input type='hidden' attribute='order_id' }
						{submit id='saveform' name='delete' value='Delete'}
					{/form}
				{/if}
			{/with}
			{include file='elements/cancelForm.tpl'}
		{/if}
	</dl>
{/content_wrapper}