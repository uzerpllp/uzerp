{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.1 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>Stock Item</th>
				<th>UoM</th>
				<th class="right">Required</th>
				<th class="right">Available</th>
				<th class="right">In Stock</th>
				<th class="right">Actual Shortfall</th>
				<th class="right">On Order</th>
				<th class="right">Expected Shortfall</th>
				<th width="1px"></th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid item=model from=$itemplan}
				<tr>
					<td>
						{link_to module='manufacturing' controller='stitems' action='view' id=$model.stitem_id value=$model.stitem}
					</td>
					<td width="50">
						{$model.uom_name}
					</td>
					<td width="50" align=right>
						{link_to module='manufacturing' controller='stitems' action='viewsales_orders' id=$model.stitem_id value=$model.required}
					</td>
					<td width="50" align=right>
						{$model.for_sale}
					</td>
					<td width="50" align=right>
						{link_to module='manufacturing' controller='stitems' action='viewbalances' id=$model.stitem_id value=$model.in_stock}
					</td>
					<td width="50" align=right>
						{link_to module='sales_order' controller='soproductlines' action='viewbydates' id=$model.stitem_id value=$model.actual_shortfall}
					</td>
					<td width="50" align=right>
						{if $model.on_order.wo_value>0}
							{link_to module='manufacturing' controller='stitems' action='viewworkorders' id=$model.stitem_id value=$model.on_order.wo_value}
						{elseif $model.on_order.po_value>0}
							{link_to module='manufacturing' controller='stitems' action='viewpurchase_orders' id=$model.stitem_id value=$model.on_order.po_value}
						{else}
							0
						{/if}
					</td>
					<td width="50" align=right>
						{$model.shortfall}
					</td>
					<td>
						<img src="/themes/default/graphics/{$model.indicator}.png" alt="{$model.indicator}" />
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