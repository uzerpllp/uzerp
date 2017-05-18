{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.13 $ *}
{content_wrapper}
	<input type="hidden" id="alternate_print_action" value="printFailureCodes" />
	{advanced_search}
	{paging}
	{data_table}
		<tr>
			<th>
				Period
			</th>
			<th class="right">
				Count
			</th>
			<th>
				Failure Description
			</th>
		</tr>
		{foreach name=datagrid key=period item=failures from=$customerservice}
			{foreach name=datagrid key=failure_id item=failure from=$failures}
				{if $failure_id == ''}
					{assign var=failure_id value='any'}
				{/if}
				{if $failure.description == ' - '}
					{assign var=description value='No Failure Codes'}
				{else}
					{assign var=description value=$failure.description}
				{/if}
				<tr>
					<td>
						{$period}
					</td>
					<td align="right">
						{$failure.count}
					</td>
					<td>
						{link_to module=$module controller=$controller action='detail' cs_failurecode_id=$failure_id start=$failure.period end=$failure.period value=$description}
					</td>
				</tr>
			{/foreach}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
	{include file='elements/data_table_actions.tpl'}
{/content_wrapper}