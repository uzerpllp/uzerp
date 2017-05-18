{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
<div id='sales_by_month'>
<dl id="view_data_left">
<table class='datagrid'>
	<tr>
		<th align=left>
			Month
		</th>
		<th class="right">
			Value
		</th>
	</tr>
	{foreach item=previous key=id from=$content.previous}
		<tr>
			<td>
				{link_to module=sales_invoicing controller=sinvoices from=$previous.start_date to=$previous.end_date value=$id}
			</td>
			</td>
			<td align=right>
				{$previous.value|string_format:"%.2f"}
			</td>
		</tr>
	{/foreach}
</table>
</dl>
<dl id="view_data_right">
<table class='datagrid'>
	<tr>
		<th align=left>
			Description
		</th>
		<th class="right">
			Value
		</th>
	</tr>
	{foreach item=current key=id from=$content.current}
		<tr>
			<td>
				{link_to module=sales_invoicing controller=sinvoices from=$current.start_date to=$current.end_date value=$id}
			</td>
			</td>
			<td align=right>
				{$current.value|string_format:"%.2f"}
			</td>
		</tr>
	{/foreach}
</table>
</dl>
</div>
