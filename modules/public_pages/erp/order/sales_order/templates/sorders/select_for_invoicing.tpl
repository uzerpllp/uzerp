{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.6 $ *}
{content_wrapper}	
	{form controller="sorders" action="saveforinvoicing"}
		<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th>Order</th>
					<th>Customer</th>
					<th class="right">Net Invoice Value</th>
					<th>Create Invoice</th>
				</tr>
			</thead>
			<tbody>
				{foreach name=datagrid item=model from=$orders}
					<tr>
						<td>
							{link_to module=$module controller=$controller action='view' id=$model->id value=$model->order_number}
						</td>
						<td>
							{link_to module='sales_ledger' controller='slcustomers' action='view' id=$model->slmaster_id value=$model->customer}
						</td>
						<td class="numeric">
							{$model->status_value($model->despatchStatus())}
						</td>
						<td>
							<input class="checkbox" type="checkbox" name="sorders[{$model->id}]" />
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
	{include file='elements/cancelForm.tpl' action='cancel'}
{/content_wrapper}