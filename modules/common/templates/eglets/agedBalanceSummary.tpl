{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<table class='datagrid'>
	<tr>
		<th>
			Period
		</th>
		<th align=right>
			Value
		</th>
	</tr>
	{foreach item=agedlist key=id from=$content}
		<tr>
			<td>
				{if $id=='0'}
					Current
				{elseif ($id==1)}
					> 1 Month
				{elseif (is_numeric($id))}
				   > {$id} Months
				{else}
					{$id}
				{/if}
			</td>
			<td align=right>
				{$agedlist|string_format:"%.2f"}
			</td>
		</tr>
	{/foreach}
</table>
