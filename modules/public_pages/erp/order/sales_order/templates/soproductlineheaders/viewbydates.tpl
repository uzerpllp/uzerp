{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.2 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$stitem}
			<dl id="view_data_left">
				{view_data attribute='item_code'  link_to='"module":"manufacturing","controller":"stitems","action":"view","id":"'|cat:$stitem->id|cat:'"'}
				{view_data attribute='description'}
				{view_data attribute="uom_name"}
			</dl>
			<dl id="view_data_right">
				{view_data attribute="comp_class"}
				{view_data attribute="type_code" label='type code'}
				{view_data attribute="product_group" label='product group'}
				{view_data attribute="currentBalance()" label='Current Balance' link_to='"module":"manufacturing","controller":"stitems","action":"viewBalances","id":"'|cat:$stitem->id|cat:'"'}
			</dl>
		{/with}
	</div>
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0" width="50%">
		<thead>
			<tr>
				<th width="75">Due Date</th>
				<th width="50">Type</th>
				<th width="50">Reference</th>
				<th class="right">Required</th>
				<th class="right">Available</th>
				<th class="right">On Order</th>
				<th class="right">Expected Stock Balance</th>
				<th class="right">Shortfall</th>
				<th width="1px"></th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid item=model from=$itemplan}
				<tr>
					<td width="75">
						{link_to module=$module controller=$controller action='index' value=$model.due_date}
					</td>
					<td width="50">
						{$model.order_type}
					</td>
					<td width="50">
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
					<td width="50" align=right>
						{$model.required}
					</td>
					<td width="50" align=right>
						{$model.available}
					</td>
					<td width="50" align=right>
						{$model.on_order}
					</td>
					<td width="50" align=right>
						{$model.in_stock}
					</td>
					<td width="50" align=right>
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