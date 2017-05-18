{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.10 $ *}	
{content_wrapper}
	{paging}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$stitem attribute="item_code" link_to='"controller":"stitems", "module":"manufacturing", "action":"view", "id":"'|cat:$stitem->id|cat:'"'}
			{view_data model=$stitem attribute="description"}
			{view_data model=$stitem attribute="lead_time"}
			{view_data model=$stitem attribute="batch_size"}
		</dl>
	</div>
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>Due Date</th>
				<th>UoM</th>
				<th class="right">On Order</th>
				<th class="right">In Stock</th>
				<th class="right">Required</th>
				<th class="right">Shortfall</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid key=due_delivery_date item=model from=$outstanding}
				<tr>
					<td width=75>
						{link_to module=$module controller=$controller action='index' due_delivery_date=$due_delivery_date value=$due_delivery_date}</a>
					</td>
					<td>
						{$model.uom_name}
					</td>
					<td width=50 align=right>
						{$model.on_order}
					</td>
					<td width=50 align=right>
						{link_to module='manufacturing' controller='stitems' action='viewBalances' id=$model.stitem_id value=$model.in_stock}
					</td>
					<td width=50 align=right>
						{link_to module=$module controller=$controller action='viewbyworders' id=$model.stitem_id value=$model.required}
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