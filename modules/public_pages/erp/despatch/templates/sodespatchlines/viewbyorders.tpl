{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.19 $ *}
{content_wrapper}
	{form controller="sodespatchlines" action="save_despatchnote"}
		<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th>Order</th>
					<th>Customer</th>
					<th>Due Date</th>
					<th colspan=3>Item Detail</th>
					<th align=center>Create Despatch note?</th>
				</tr>
			</thead>
			<tbody>
				{foreach name=datagrid item=model from=$orders}
					<tr>
						<td>
							<a href="?module=sales_order&controller=SOrders&action=view&id={$model.order_id}">
								{$model.order_number}
							</a>
						</td>
						<td>
							{$model.customer}
						</td>
						<td colspan=5>
							{$model.del_address}
							<hr>
						</td>
					</tr>
					{foreach name=datagrid item=submodel key=key from=$model.line_number}
						<tr>
							<td colspan=2>
							</td>
							<td>
								{$model.due_despatch_date}
							</td>
							<td>
								{$key}
								{$submodel.item_description}
							</td>
							<td width=50 align=right>
								{$submodel.required}
							</td>
							<td>
								{$submodel.stuom}
							</td>
							<td align=center>
								{if $submodel.despatch && $submodel.delivery_note==''}
									<input class="checkbox" type="checkbox" name="sodespatchlines[{$submodel.id}]" />
									<input type="hidden" name="despatch_action[{$submodel.id}]" value="{$model.despatch_action}">
								{elseif $submodel.delivery_note<>''}
									selected on DN {$submodel.delivery_note}<br>
								{/if}
								{if !$submodel.despatch}
									<strong>Warning - Insufficent Stock</strong>
								{/if}
							</td>
						</tr>
					{/foreach}
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