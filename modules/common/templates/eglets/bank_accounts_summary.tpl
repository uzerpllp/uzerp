{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
<table class='datagrid'>
	<tr>
		<th>
			Account
		</th>
		<th class=right>
			Current Balance
		</th>
	</tr>
	{foreach item=cbaccount key=id from=$content}
		<tr>
			<td>
				{link_to module=cashbook controller=bankaccounts action=view id=$cbaccount->id value=$cbaccount->name}
			</td>
			<td align=right>
				{$cbaccount->balance}
			</td>
		</tr>
	{/foreach}
</table>
