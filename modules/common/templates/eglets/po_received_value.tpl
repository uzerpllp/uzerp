{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<table class='datagrid'>
	<tr>
		<th align=left>
			Period
		</th>
		<th class=right>
			Value
		</th>
	</tr>
	{foreach item=received key=id from=$content}
		<tr>
			<td align=left>
				{$id}
			</td>
			<td align=right>
				{$received}
			</td>
		</tr>
	{/foreach}
</table>
