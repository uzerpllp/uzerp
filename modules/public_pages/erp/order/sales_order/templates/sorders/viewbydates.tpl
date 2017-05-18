{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.8 $ *}
{content_wrapper}
	<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th>Due Date</th>
				<th>Stock Item</th>
				<th class="right">Required</th>
				<th class="right">Available</th>
				<th class="right">On Order</th>
				<th class="right">Expected Stock Balance</th>
				<th class="right">Shortfall</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=datagrid key=due_date item=model from=$outstanding}
				<tr>
					<td width=75>
						{link_to module=$module controller=$controller action='index' due_date=$due_date value=$model.due_date}
					<td>
					{link_to module='manufacturing' controller='stitems' action='view' id=$model.stitem_id value=$model.stitem}
					<td width=50 align=right>
						{link_to module=$module controller=$controller action='viewbyorders' id=$model.stitem_id value=$model.required}
					</td>
					<td width=50 align=right>
						{$model.for_sale}
					</td>
					<td width=50 align=right>
						{link_to module='manufacturing' controller='stitems' action='viewWorkorders' id=$model.stitem_id value=$model.on_order}
					</td>
					<td width=50 align=right>
						{link_to module='manufacturing' controller='stitems' action='viewBalances' id=$model.stitem_id value=$model.in_stock}
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