{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.14 $ *}
{content_wrapper}
	{form controller="sorders" action="releaseorders"}
		<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th>Due Date</th>
					<th>Order</th>
					<th>Customer</th>
					<th>Stock Item</th>
					<th>Status</th>
					<th class="right">Required</th>
					<th class="right">Available</th>
					<th class="right">On Order</th>
					<th class="right">Expected Stock Balance</th>
					<th class="right">Shortfall</th>
					<th>Release for Despatch</th>
				</tr>
			</thead>
			<tbody>
				{assign var="gridrow_class" value=""}
				{foreach name=datagrid item=model from=$orders}
					<tr>
						<td>
							{link_to module=$module controller=$controller action='index' due_date=$model.despatch_date value=$model.due_despatch_date}
						</td>
						<td>
							{link_to module=$module controller=$controller action='view' id=$model.order_id value=$model.order_number}
						</td>
						<td>
							{link_to module='sales_ledger' controller='slcustomers' action='view' id=$model.slmaster_id value=$model.customer}
						</td>
						<td>
							{link_to module='manufacturing' controller='stitems' action='view' id=$model.stitem_id value=$model.stitem}
						</td>
						<td>
							{if $model.despatch_number > 0}
								Selected on DN{$model.despatch_number}
							{elseif $model.account_status == 'S'}
								Account on Stop
							{else}
								{$model.status}
							{/if}
						</td>
						<td width=50 align=right>
							{link_to module=$module controller=$controller action='viewbydates' id=$model.stitem_id value=$model.required}
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
						<td align="center">
							{if $model.despatch_number == -1 && $model.account_status != 'S'}
								{if $model.despatch}
									<input class="checkbox" type="checkbox" name="sorders[{$model.id}]" checked/>
								{else}
									<input class="checkbox" type="checkbox" name="sorders[{$model.id}]" />
								{/if}
							{/if}
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
		{submit}
	{/form}
{/content_wrapper}