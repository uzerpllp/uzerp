{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
<table class='datagrid'>
	<tr>
		<th width=10 align=center>
			Order
		</th>
		<th align=left>
			Supplier
		</th>
		<th>
			Description
		</th>
		<th class=right>
			Qty
		</th>
	</tr>
	{foreach item=porder key=id from=$content}
		<tr>
			<td width=10 align=right>
				{link_to module=purchase_order controller=porders action=view id=$porder->order_id value=$porder->order_number}
			</td>
			<td>
				{if strlen($porder->supplier)>23}
					{$porder->supplier|substr:0:20}...
				{else}
					{$porder->supplier}
				{/if}
			</td>
			<td>
				{if strlen($porder->item_description)>23}
					{$porder->item_description|substr:0:20}...
				{else}
					{$porder->item_description}
				{/if}
			</td>
			<td align=right>
				{$porder->order_qty}
			</td>
		</tr>
	{/foreach}
</table>
