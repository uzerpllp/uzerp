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
		<th width=10 align=center>
			Date
		</th>
		<th width=10 align=center>
			Due
		</th>
		<th align=left>
			Supplier
		</th>
	</tr>
	{foreach item=porder key=id from=$content}
		<tr>
			<td width=10>
				{link_to module=purchase_order controller=porders action=view id=$porder->id value=$porder->order_number}
			</td>
			<td>
				{$porder->order_date|un_fix_date}
			</td>
			<td>
				{$porder->due_date|un_fix_date}
			</td>
			<td>
				{if strlen($porder->supplier) > 43}
					{$porder->supplier|substr:0:40}...
				{else}
					{$porder->supplier}
				{/if}
			</td>
		</tr>
	{/foreach}
</table>
