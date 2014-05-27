{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.11 $ *}	
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$stitem}
				{view_data attribute='item_code'  link_to='"module":"manufacturing","controller":"stitems","action":"view","id":"'|cat:$stitem->id|cat:'"'}
				{view_data attribute='description'}
				{view_data attribute="lead_time"}
				{view_data attribute="batch_size"}
				{view_data attribute="uom_name"}
				{view_data attribute="comp_class"}
				{view_data attribute="sttypecode" label='type code'}
				{view_data attribute="stproductgroup" label='product group'}
				{view_data value=$model->currentBalance() label='Current Balance' link_to='"module":"manufacturing","controller":"stitems","action":"viewBalances","id":"'|cat:$stitem->id|cat:'"'}
			{/with}
		</dl>
	</div>
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0" width=200>
		<thead>
			<tr>
				<th>Due Date</th>
				<th>Type</th>
				<th>Reference</th>
				<th>ST Item</th>
				<th class="right">Required</th>
				<th class="right">On Order</th>
				<th class="right">Expected Stock</th>
				<th class="right">Shortfall</th>
				<th width="1px"></th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid item=model from=$itemplan}
				<tr>
					<td width=75>
						{$model.due_date}</a>
					</td>
					<td width=50>
						{$model.order_type}
					</td>
					<td width=50>
						{if $model.order_type=='WO'}
							{assign var='linksubmodule' value='manufacturing'}
							{assign var='linkcontroller' value='mfworkorders'}
						{elseif $model.order_type=='SO'}
							{assign var='linksubmodule' value='sales_order'}
							{assign var='linkcontroller' value='sorders'}
						{elseif $model.order_type=='PO'}
							{assign var='linksubmodule' value='purchase_order'}
							{assign var='linkcontroller' value='porders'}
						{/if}
						{link_to module=$linksubmodule controller=$linkcontroller action='view' id=$model.reference_id value=$model.reference}
					</td>
					<td width=75>
						{$model.stitem_code}
					</td>
					<td width=50 align=right>
						{$model.required}
					</td>
					<td width=50 align=right>
						{$model.on_order}
					</td>
					<td width=50 align=right>
						{$model.in_stock}
					</td>
					<td width=50 align=right>
						{$model.shortfall}
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	{paging}
{/content_wrapper}