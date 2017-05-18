{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.13 $ *}	
{content_wrapper}
	{advanced_search}
	{paging}
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>Stock Item</th>
				<th class="right">Lead Time</th>
				<th class="right">Batch Size</th>
				<th>UoM</th>
				<th class="right">Required</th>
				<th class="right">On Order</th>
				<th class="right">In Stock</th>
				<th class="right">Shortfall</th>
				<th width="1px"></th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid item=model from=$itemplan}
				<tr>
					<td>
						{link_to module='manufacturing' controller='stitems' action='view' id=$model.stitem_id value=$model.stitem}
					</td>
					<td class="numeric">
						{$model.lead_time}
					</td>
					<td class="numeric">
						{$model.batch_size}
					</td>
					<td>
						{$model.uom_name}
					</td>
					<td width=50 class='numeric'>
						{$model.required}
					</td>
					<td width=50 class='numeric'>
						{link_to module=$module controller= $controller action='viewbydates' id=$model.stitem_id value=$model.on_order}
					</td>
					<td width=50 class='numeric'>
						{link_to module='manufacturing' controller='stitems' action='viewBalances' id=$model.stitem_id value=$model.in_stock}
					</td>
					<td width=50 class='numeric'>
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