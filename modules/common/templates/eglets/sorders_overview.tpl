{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
<div id='sorders_index'>
<table class='datagrid'>
	<tr>
		<th align=left>
			Description
		</th>
		<th class="right">
			Value
		</th>
	</tr>
	{foreach item=sorder key=id from=$content.main}
		<tr>
			<td>
				<a class="ajax" href="/?module=sales_order&controller=SOrders&action=orderitemsummary&period={$id}&type={$content.type}&page=1&_target=sorders_item_overview">
					{$id}
				</a>
			</td>
			</td>
			<td align=right>
				{$sorder|string_format:"%.2f"}
			</td>
		</tr>
	{/foreach}
</table>
</div>