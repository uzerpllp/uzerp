{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
<table class='datagrid'>
	<tr>
		<th width=10 align=center>
			Invoice Number
		</th>
		<th align=left>
			{$type_label}
		</th>
		<th align=center>
			Due Date
		</th>
		<th align=right>
			Value
		</th>
	</tr>
	{foreach item=line key=id from=$content}
		<tr>
			<td width=10 align=right>
				{link_to module=$module controller=$controller action="view" id=$line->id value=$line->invoice_number}
			</td>
			<td>
				{$line->$type_field}
			</td>
			<td align=center>
				{$line->due_date}
			</td>
			<td align=right>
				{$line->base_gross_value|string_format:"%.2f"}
			</td>
		</tr>
	{/foreach}
</table>