{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.9 $ *}	
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$stitem attribute="item_code"}
			{view_data model=$stitem attribute="description"}
			{view_data model=$stitem attribute="lead_time"}
			{view_data model=$stitem attribute="batch_size"}
		</dl>
	</div>
	<b>is required for the following Works Orders</b>
	{paging}
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>Works Order</th>
				<th>Due Date</th>
				<th>Stock Item</th>
				<th>UoM</th>
				<th>Order Qty</th>
				<th>Made Qty</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid item=model from=$wostructures}
				<tr>
					<td>
						{link_to module='manufacturing' controller='mfworkorders' action='view' id=$model->work_order_id value=$model->work_order_id}
					</td>
					<td>
						{$model->required_by}</a>
					</td>
					<td>
						{link_to module='manufacturing' controller='stitems' action='view' id=$model->stitem_id value=$model->stitem}
					</td>
					<td>
						{$model->uom}</a>
					</td>
					<td width=50 align=right>
							{$model->order_qty}
					</td>
					<td width=50 align=right>
							{$model->made_qty}
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