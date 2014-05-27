{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.13 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row model=$models.SInvoiceLine}
			{heading_cell field='product_group'}
				Product Group
			{/heading_cell}
			{heading_cell field='customer'}
				Customer
			{/heading_cell}
			{heading_cell field='stitem'}
				Item
			{/heading_cell}
			{heading_cell field='order_number'}
				Order Number
			{/heading_cell}
			{heading_cell field='despatch_number'}
				Despatch Number
			{/heading_cell}
			{heading_cell field='due_despatch_date'}
				Due Despatch Date
			{/heading_cell}
			{heading_cell field='despatch_date'}
				Despatch Date
			{/heading_cell}
			{heading_cell field='order_qty' class='right'}
				Order Quantity
			{/heading_cell}
			{heading_cell field='despatch_qty' class='right'}
				Despatch Quantity
			{/heading_cell}
			{heading_cell field='failurecode'}
				Failure Code
			{/heading_cell}
			{heading_cell}
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=cs from=$sinvoicelines}
			{grid_row}
				{grid_cell model=$cs field='product_group'}
					{$cs->product_group}
				{/grid_cell}
				{grid_cell model=$cs field='customer'}
					{$cs->customer}
				{/grid_cell}
				{grid_cell model=$cs field='stitem'}
					{$cs->stitem}
				{/grid_cell}
				<td>
					{link_to module='sales_order' controller='sorders' action='view' id=$cs->order_id value=$cs->order_number}
				</td>
				<td>
					{link_to module='despatch' controller='sodespatchlines' action='view' id=$cs->id value=$cs->despatch_number}
				</td>
				{grid_cell model=$cs field='due_despatch_date'}
					{$cs->due_despatch_date}
				{/grid_cell}
				{grid_cell model=$cs field='despatch_date'}
					{$cs->despatch_date}
				{/grid_cell}
				{grid_cell model=$cs field='order_qty' class='numeric'}
					{$cs->order_qty}
				{/grid_cell}
				{grid_cell model=$cs field='despatch_qty' class='numeric'}
					{$cs->despatch_qty}
				{/grid_cell}
				{grid_cell model=$cs field='failurecode'}
					{$cs->failurecode}
				{/grid_cell}
				<td>
					{if $cs->despatch_date>$cs->due_despatch_date || $cs->order_qty>$cs->despatch_qty}
						{link_to module=$module controller=$controller action='updatefailure' id=$cs->id value='amend'}
					{/if}
				</td>
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
	{include file='elements/data_table_actions.tpl'}
{/content_wrapper}