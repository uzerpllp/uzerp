{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.17 $ *}
{content_wrapper}
	{advanced_search}
	<p><strong>Transaction Details</strong></p>
	{input type="hidden" attribute="id" value=$id}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="reference" id=$id }
				Reference
			{/heading_cell}
			{heading_cell field="cb_account" id=$id }
				Account
			{/heading_cell}
			{heading_cell field="transaction_date" id=$id }
				Transaction Date
			{/heading_cell}
			{heading_cell field="company" id=$id }
				Company
			{/heading_cell}
			{heading_cell field="person" id=$id }
				Person
			{/heading_cell}
			{heading_cell field="description" id=$id }
				Description
			{/heading_cell}
			{heading_cell field="ext_reference" id=$id }
				Ext Reference
			{/heading_cell}
			{heading_cell field="payment_type" id=$id }
				Payment Type
			{/heading_cell}
			{heading_cell field="gross_value" id=$id class='right'}
				Value
			{/heading_cell}
			{heading_cell field="currency" id=$id }
				currency
			{/heading_cell}
			{heading_cell field="status" id=$id }
				Status
			{/heading_cell}
			{heading_cell field="source" id=$id }
				Source
			{/heading_cell}
			{heading_cell field="type" id=$id }
				Type
			{/heading_cell}
			{heading_cell field="statement_date" id=$id }
                Stmt Date
            {/heading_cell}
            {heading_cell field="statement_page" id=$id }
                Stmt Page
            {/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$cbtransactions}
			{assign var=totalValue value=$totalValue+$model->gross_value}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="reference"}
					{$model->reference}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="cb_account" no_escape='true'}
					{link_to module=$module controller='Bankaccounts' action='view' id=$model->cb_account_id value=$model->cb_account}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="transaction_date"}
					{$model->transaction_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="company"}
					{$model->company}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="person"}
					{$model->person}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="description"}
					{$model->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="ext_reference"}
					{$model->ext_reference}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="payment_type"}
					{$model->payment_type}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="gross_value"}
					{$model->gross_value}
				{/grid_cell}
				{grid_cell model=$model cell_num=8 field="currency"}
					{$model->currency}
				{/grid_cell}
				{grid_cell model=$model cell_num=9 field="status"}
					{$model->getFormatted('status')}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="source"}
					{$model->getFormatted('source')}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="type"}
					{$model->getFormatted('type')}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="statement_date"}
                    {$model->getFormatted('statement_date')}
                {/grid_cell}
                {grid_cell model=$model cell_num=10 field="statement_page"}
                    {$model->getFormatted('statement_page')}
                {/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
		{if $cbtransactions->count()>0}
			<tr>
		    	<td colspan="8" style="text-align: right;">Total Value for Page</td>
		    	<td colspan="4">{$totalValue|string_format:"%.2f"}</td>
		  	</tr>
		{/if}
	{/data_table}
	<div id="data_grid_footer" class="clearfix">
		{paging}
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}