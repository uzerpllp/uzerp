{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	<dl id="view_data_left">
		{form controller="sinvoicelines" action="save"}
			{with model=$sinvoice legend="Sales Invoice Header"}
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='slmaster_id' }
				{include file='elements/auditfields.tpl' }
			{/with}
			{with model=$models.SInvoiceLine legend="SInvoiceLine Details"}
				{input type='hidden' attribute='id' }
				{input type='hidden' attribute='invoice_id' }
				{input type="hidden" attribute="status"}
				{include file='elements/auditfields.tpl' }
				{input type="hidden" attribute="line_number"}
				{if $model->line_number != '' }
					{view_data attribute="line_number" label='Invoice Line Number'}
				{/if}
				{if !is_null($model->order_line_id)}
					{view_data attribute='sales_order'}
					{view_data attribute='order_line_number' label='Sales Order Line Number'}
					{view_data attribute='product_description' label='Product'}
				{else}
					{if isset($display_fields.product_search)}
						<input type='hidden' id=prod_search_limit name=limit value={$config.AUTOCOMPLETE_SELECT_LIMIT}>
						{input type='text' attribute="product_search" value=$product_search}
					{/if}
					{select attribute="productline_id" options=$productline_options nonone=true}
				{/if}
				{input type="text" attribute="delivery_note" class="copy_value" size="20"}
				{input type="text" attribute="description"}
				{if !is_null($model->order_line_id)}
					{view_data attribute='sales_qty'}
					{view_data attribute='uom_name'}
					{view_data attribute='sales_price'}
				{else}
					{input type="text" attribute="sales_qty" class="sales_qty numeric" }
					{select attribute="stuom_id" class="short_select nonone" nonone=true options=$stuom_options}
					{input type="text" attribute="sales_price" class="price numeric" size="10" }
				{/if}
				{if $model->delivery_note==''}
					{if $model->move_stock=='t'}
						{input type="checkbox" attribute="move_stock" checked=true}
					{else}
						{input type="checkbox" attribute="move_stock"}
					{/if}
				{/if}
				{select attribute='glaccount_id' class="copy_value account_centre" options=$glaccount_options}
				{select attribute='glcentre_id' class='account_centre' options=$glcentre_options}
				{select attribute='tax_rate_id' nonone=true options=$taxrate_options class='tax_rate'}
				{input type="text" attribute="net_value" class="net_value numeric" size="20" readonly=true}
			{/with}
			{if !$dialog}
				{submit}
				{submit name="saveAnother" value="Save and Add Another"}
			{/if}
		{/form}
		{if !$dialog}
			{with model=$models.SInvoiceLine legend="Sales Invoice Line Details"}
				{if $model->id!=''}
					{form id='delete_form' controller="sinvoicelines" action="delete"}
						{input type='hidden' attribute='id' }
						{input type='hidden' attribute='invoice_id' }
						{submit id='saveform' name='delete' value='Delete'}
					{/form}
				{/if}
			{/with}
			{include file='elements/cancelForm.tpl'}
		{/if}
	</dl>
{/content_wrapper}