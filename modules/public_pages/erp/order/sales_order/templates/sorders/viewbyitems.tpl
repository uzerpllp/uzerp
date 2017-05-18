{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.15 $ *}
{content_wrapper}
	<input type="hidden" id="print_force_index" value="true" />
	<input type="hidden" id="alternate_print_action" value="viewbyitems" />
	{advanced_search}
	{paging}
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>Stock Item</th>
				<th>UoM</th>
				<th class="right">Minimum Stock</th>
				<th class="right">Required</th>
				<th class="right">Available</th>
				<th class="right">In Stock</th>
				<th class="right">Actual Shortfall</th>
				<th class="right">On Order</th>
				<th class="right">Expected Shortfall</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid key=id item=model from=$orders}
				<tr>
					<td>
						{link_to module='manufacturing' controller='stitems' action='view' id=$id value=$model.stitem}
					</td>
					<td>
						{$model.uom_name}
					</td>
					<td width=50 align=right>
						{$model.min_qty}
					</td>
					<td width=50 align=right>
						{link_to module='manufacturing' controller='stitems' action='viewsales_orders' id=$id value=$model.required}
					</td>
					<td width=50 align=right>
						{$model.for_sale}
					</td>
					<td width=50 align=right>
						{link_to module='manufacturing' controller='stitems' action='viewbalances' id=$id value=$model.in_stock}
					</td>
					<td width=50 align=right>
						{link_to module=$module controller=$controller action='viewbydates' id=$id value=$model.actual_shortfall}
					</td>
					<td width=50 align=right>
						{if $model.on_order.wo_value>0}
							{link_to module='manufacturing' controller='stitems' action='viewworkorders' id=$id value=$model.on_order.wo_value}
						{elseif $model.on_order.po_value>0}
							{link_to module='manufacturing' controller='stitems' action='viewpurchase_orders' id=$id value=$model.on_order.po_value}
						{else}
							0
						{/if}
					</td>
					<td width=50 align=right>
						{$model.shortfall}
					</td>
					<td>
						<img src="/assets/graphics/{$model.indicator}.png" alt="{$model.indicator}" />
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