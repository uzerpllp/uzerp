{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
<table class='datagrid'>
	<tr>
		<th width=10 align=center>
			Order
		</th>
		<th align=left>
			Supplier
		</th>
		<th>
			Ordered
		</th>
		<th>
			Due
		</th>
		<th class=right>
			Value
		</th>
	</tr>
	{foreach item=porder key=id from=$content}
		<tr>
			<td width=10 align=right>
				{link_to module=purchase_order controller=porders action=view id=$porder->id value=$porder->order_number}
			</td>
			<td>
				{$porder->supplier}
			</td>
			<td>
				{$porder->order_date}
			</td>
			<td>
				{$porder->due_date}
			</td>
			<td align=right>
				{$porder->base_net_value|string_format:"%.2f"}
			</td>
		</tr>
	{/foreach}
</table>
