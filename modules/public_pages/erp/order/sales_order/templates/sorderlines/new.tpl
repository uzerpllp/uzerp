{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<dl id="view_data_left">
		{form controller="sorderlines" action="save"}
			{with model=$sorder legend="Sales Order Header"}
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='slmaster_id' }
				{include file='elements/auditfields.tpl' }
			{/with}
			{with model=$models.SOrderLine  legend="Sales Order Line Details"}
				{if isset($display_fields.product_search) && $action != 'edit'}
					<input type='hidden' id=prod_search_limit name=limit value={$config.AUTOCOMPLETE_SELECT_LIMIT}>
				{else}
					<input type='hidden' id=prod_search_limit name=limit value=0>
				{/if}
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='order_id' }
				{input type="hidden" attribute="status"}
				{include file='elements/auditfields.tpl' }
				{input type="hidden" attribute="line_number"}
				{if $model->line_number != '' }
					{view_data attribute="line_number"}
				{/if}
				{* The following is a hack to prevent date picker showing on dialog open *}
				<span class="ui-helper-hidden-accessible"><input type="text"/></span>
				{input type='date' attribute="due_delivery_date"}
				{input type='date' attribute="due_despatch_date"}
				{if isset($display_fields.product_search) && $action != 'edit'}
					{input type='text' attribute="product_search" value=$product_search}
				{/if}
				{if $action != 'edit'}
					{select attribute="productline_id" label='product' options=$productline_options nonone=true}
				{else}
					{view_data attribute="productline_id" label='product'}
				{/if}
				{input type="hidden" attribute="item_description" }
				{input type="text" attribute="description" }
				{view_data attribute='sales_stock' label='Pickable Stock' value=$sales_stock}
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
				{textarea attribute="note" class="note" size="10" }
				{if $model->id>0}
					{input type="checkbox" attribute="cancel_line" class="checkbox" label='Cancel Line'}
				{/if}
			{/with}
			{if !$dialog}
				{submit}
				{submit name="saveAnother" value="Save and Add Another"}
			{/if}
		{/form}
		{if !$dialog}
			{with model=$models.SOrderLine  legend="Sales Order Line Details"}
				{if $model->id!=''}
					{form id='delete_form' controller="sorderlines" action="delete"}
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