{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{data_table}
		{heading_row}
			{heading_cell field="customer"}
				Method
			{/heading_cell}
			{heading_cell field="name"}
				Name
			{/heading_cell}
			{heading_cell field="transaction_type"}
				Detail
			{/heading_cell}
		{/heading_row}
		{foreach name=transactions item=contact from=$contactdetails}
			<tr>
				<td>{$contact->getFormatted('type')}</td>
				<td>{$contact->name}</td>
				<td>{$contact->contact}</td>
			</tr>
		{/foreach}
	{/data_table}
{/content_wrapper}