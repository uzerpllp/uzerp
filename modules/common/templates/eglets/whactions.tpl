{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
<table class='datagrid'>
	<tr>
		<th width=10 align=center>
			Action
		</th>
	</tr>
	{foreach item=action key=id from=$content}
		<tr>
			<td width=10 align=left>
				{if $module=='dashboard'}
					{assign var=module value='manufacturing'}
				{/if}
				{link_to module=$module controller=sttransactions action=new whaction_id=$action->id value=$action->action_name}
			</td>
		</tr>
	{/foreach}
</table>