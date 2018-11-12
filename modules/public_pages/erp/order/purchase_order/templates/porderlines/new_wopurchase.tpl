{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
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

					{* The following is a hack to prevent date picker showing on dialog open *}
					<span class="ui-helper-hidden-accessible"><input type="text"/></span>
					{input type='date' attribute="due_delivery_date" value=$default_due_date}
					{select attribute='mf_workorders_id' label='Work Order' options=$wo_options nonone=true}
    				{if $action != 'edit'}
    					<dt>
							<label for="POrderLine_productline_id">Operation</label>
						</dt>
						<dd>
							<select id="POrderLine_productline_id" data-field="productline_id" name="POrderLine[productline_id]" class="nonone">
								{foreach from=$op_options key=pl item=op name=pl}
								{if $smarty.foreach.pl.first}{assign var='first_op' value=$op.op_id}{/if}
								<option value="{$pl}" data-opid="{$op.op_id}">{$op.op_no} - {$op.description}</option>
								{/foreach}
							</select>
							<input type="hidden" id="POrderLine_mf_operations_id" data-field="mf_operations_id" name="POrderLine[mf_operations_id]" value="{$first_op}"> 
						</dd>
						
    				{else}
    					{view_data attribute="productline_id" label='Product'}
    				{/if}
					{input type="text" attribute="description" value=$default_description}
					{input type="text" attribute="revised_qty" label="Quantity" class="order_qty numeric" value=$work_order_qty}
					{if $model->id!=''}
						{view_data attribute="order_qty" label="Original Quantity"}
					{/if}
					{input type="text" attribute="price" class="price numeric" size="10" value=$default_price}
					{select attribute="stuom_id" class="short_select" options=$stuom_options}
					{select attribute="glaccount_id" options=$glaccount_options }
					{select attribute="glcentre_id" options=$glcentre_options}
					{select attribute="tax_rate_id" options=$taxrate_options class='tax_rate'}
					{input type="text" attribute="net_value" class="net_value numeric" size="10" readonly=true value=$net_value}
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