{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.18 $ *}
{content_wrapper}
	<input type="hidden" id="alternate_print_action" value="printCustomerService" />
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			<th>
				Product Group
			</th>
			<th>
				Customer
			</th>
			<th class="right">
				On Time
			</th>
			<th class="right">
				In Full
			</th>
			<th class="right">
				On Time/In Full
			</th>
		{/heading_row}
		{foreach name=datagrid key=product_group item=customers from=$customerservice}
			{foreach name=datagrid key=customer item=service from=$customers}
				{if $product_group == 'Grand Total' || $customer == 'total'}
					<tr class="sub_total">
						<td>
							{$product_group}
						</td>
				{else}
					<tr>
						<td>
							{link_to module=$module controller=$controller action='detail' product_group=$product_group slmaster_id=$customer value=$product_group}
						</td>
				{/if}
					<td>
						{$service.customer}
					</td>
					<td align="right">
						{$service.ontime_c} %
					</td>
					<td align="right">
						{$service.infull_c} %
					</td>
					<td align="right">
						{$service.ontime_infull_c} %
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