{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
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
		<th align=left>
			Required
		</th>
		<th align=left>
			Made
		</th>
		<th align="center" colspan=3>
			-------- Action --------
		</th>
	</tr>
	{foreach item=worder key=id from=$content}
		<tr>
			<td width=10 align=right>
				{link_to module=$module submodule=$submodule controller="mfworkorders" action="view" id=$worder->id value=$worder->wo_number}
			</td>
			<td>
				{$worder->item_code|cat:': '|cat:$worder->stitem|truncate:50:"...":true}
			</td>
			<td width=10 align=right>
				{$worder->order_qty}
			</td>
			<td width=10 align=right>
				{$worder->made_qty}
			</td>
			<td width=10 align=right>
				{link_to module=$module submodule=$submodule controller="mfworkorders" action="bookproduction" id=$worder->id stitem_id=$worder->stitem_id value='Book'}
			</td>
			<td width=10 align=right>
				{link_to module=$module submodule=$submodule controller="mfworkorders" action="issues_returns" id=$worder->id type='I' value='issue'}
			</td>
			<td width=10 align=right>
				{link_to module=$module submodule=$submodule controller="mfworkorders" action="issues_returns" id=$worder->id type='X' value='return'}
			</td>
		</tr>
	{/foreach}
</table>