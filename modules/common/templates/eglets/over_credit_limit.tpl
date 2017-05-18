{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
<table class='datagrid'>
	<tr>
		<th>
			Accounts
		</th>
		<th class="right">
			Credit Limit
		</th>
		<th class="right">
			Balance
		</th>
		<th class="right">
			Outstanding Orders
		</th>
	</tr>
	{foreach item=v from=$content}
		<tr>
			<td>
				{link_to module=sales_ledger controller=slcustomers action=view id=$v->id value=$v->name}
			</td>
			<td align=right>
				{$v->credit_limit|string_format:"%.2f"}
			</td>
			<td align=right>
				{$v->outstanding_balance|string_format:"%.2f"}
			</td>
			<td align=right>				
				{$v->getOutstandingOrders()|string_format:"%.2f"}
			</td>
		</tr>
	{/foreach}
</table>