{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
<table class='datagrid'>
	<tr>
		<th width=10 align=center>
			WO
		</th>
		<th align=left>
			Stock Item
		</th>
	</tr>
	{foreach item=worder key=id from=$content}
		<tr>
			<td width=10 align=right>
				{link_to module=$module submodule=$submodule controller=mfworkorders action=printaction printaction=printdocumentation id=$worder->id value=$worder->wo_number}
			</td>
			<td>
				{$worder->item_code|cat:': '|cat:$worder->stitem|truncate:20:"...":true}
			</td>
		</tr>
	{/foreach}
</table>
