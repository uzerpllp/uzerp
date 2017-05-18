{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{advanced_search}
	<p><strong>VAT Return</strong></p>
	{if !$tax_period_closed}
		<p style="color: red"><strong>Warning:</strong> Tax period is not closed therefore the figures below may not be final.</p>
	{/if}
	{data_table}
		{heading_row}
			{foreach from=$titles key=k item=value}
				{heading_cell}
					{$value}
				{/heading_cell}
			{/foreach}
		{/heading_row}
		<tr>
			{foreach from=$boxes item=value}
				<td>
					{$value[title]}
					{$symbol|escape}{$value.value|string_format:"%.2f"}
				</td>
			{/foreach}
		</tr>
	{/data_table}
{/content_wrapper}