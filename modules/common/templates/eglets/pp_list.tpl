{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
<table class='datagrid'>
	<tr>
		<th align=left>
			Company
		</th>
		<th width=10 align=center>
			Source
		</th>
		<th>
			Due
		</th>
		<th align=right>
			Value
		</th>
	</tr>
	{foreach item=pp key=id from=$content}
		<tr>
			<td>
				{link_to module=$module submodule=$submodule controller=periodicpayments action=makepayments company_id=$pp->company_id cb_account_id=$pp->cb_account_id frequency=$pp->frequency source=$pp->source from_date=$pp->next_due_date to_date=$pp->next_due_date value=$pp->company}
			</td>
			<td width=10 align=right>
				{link_to module=$module submodule=$submodule controller=periodicpayments action=index source=$pp->source value=$pp->source}
			</td>
			<td>
				{$pp->next_due_date}
			</td>
			<td align=right>
				{$pp->getFormatted('gross_value')}
			</td>
		</tr>
	{/foreach}
</table>
