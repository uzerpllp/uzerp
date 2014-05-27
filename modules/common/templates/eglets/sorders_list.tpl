{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{assign var=date_label value=$content->collection_date_label}
<table class='datagrid'>
	<tr>
		<th width=10 align=center>
			Order
		</th>
		<th align=left>
			Customer
		</th>
		<th>
			Ordered
		</th>
		<th>
			{$date_label|prettify}
		</th>
		<th class="right">
			Value
		</th>
	</tr>
	{foreach item=sorder key=id from=$content}
		<tr>
			<td width=10 align=right>
				{link_to module=sales_order controller=sorders action=view id=$sorder->id value=$sorder->order_number}
			</td>
			<td>
				{$sorder->customer|truncate:25}
			</td>
			<td>
				{$sorder->order_date}
			</td>
			<td>
				{$sorder->$date_label}
			</td>
			<td align=right>
				{$sorder->base_net_value|string_format:"%.2f"}
			</td>
		</tr>
	{/foreach}
</table>
