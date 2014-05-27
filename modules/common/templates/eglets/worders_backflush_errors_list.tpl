{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
<table class='datagrid'>
	<tr>
		<th align=left>
			Stock Item
		</th>
	</tr>
	{foreach item=sttransaction key=id from=$content}
		<tr>
			<td>
				{link_to module=manufacturing controller=Sttransactions action=view id=$sttransaction->id value=$sttransaction->created|un_fix_date} - {$sttransaction->stitem|truncate:20}
			</td>
		</tr>
	{/foreach}
</table>