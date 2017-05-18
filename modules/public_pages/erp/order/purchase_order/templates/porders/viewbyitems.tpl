{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.11 $ *}	
{content_wrapper}
	{advanced_search}
	{paging}
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>Stock Item</th>
				<th>Lead Time</th>
				<th>Batch Size</th>
				<th>UoM</th>
				<th class="right">On Order</th>
				<th class="right">In Stock</th>
				<th class="right">Required</th>
				<th class="right">Shortfall</th>
				<th class="right"></th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid item=model key=stitem_id from=$orders}
				<tr>
					<td>
						{link_to module='manufacturing' controller='stitems' action='view' id=$stitem_id value=$model.stitem}
					</td>
					<td>
						{$model.lead_time}
					</td>
					<td>
						{$model.batch_size}
					</td>
					<td>
						{$model.uom_name}
					</td>
					<td width=50 align=right>
						{link_to module=$module controller=$controller action='viewbydates' id=$stitem_id value=$model.on_order}
					</td>
					<td width=50 align=right>
						{link_to module='manufacturing' controller='stitems' action='viewBalances' id=$stitem_id value=$model.in_stock}
					</td>
					<td width=50 align=right>
						{link_to module=$module controller=$controller action='viewbyworders' id=$stitem_id value=$model.required}
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