{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.13 $ *}
{content_wrapper}
	{form controller="bankaccounts" action="save_reconciliation"}
		<div id="view_page" class="clearfix">
			<dl id="view_data_left">
				{with model=$CBAccount}
					{input type="hidden" attribute="id"}
					{view_data label="Bank Account" value=$model->name}
					{view_data attribute="statement_balance" id=''}
					{input type="hidden" attribute=statement_balance id="statement_balance" name="current_statement_balance"}
					{input type="text" attribute="statement_balance" label="new_statement_balance"}
					{input type='date' attribute="statement_date"}
					{input type="text" attribute="statement_page"}
				{/with}
			</dl>
		</div>
		{data_table}
			{heading_row}
				{heading_cell field="transaction_date"}
					Transaction Date
				{/heading_cell}
				{heading_cell field="reference"}
					Reference
				{/heading_cell}
				{heading_cell field="company"}
					Company
				{/heading_cell}
				{heading_cell field="person"}
					person
				{/heading_cell}
				{heading_cell field="ext_reference"}
					Ext Reference
				{/heading_cell}
				{heading_cell field="payment_type"}
					Payment Type
				{/heading_cell}
				{heading_cell field="desc"}
					Description
				{/heading_cell}
				{heading_cell field="gross_value"}
					Gross Value
				{/heading_cell}
				<th data-column="select">
			  		<a href="#" class="select-all dont-sort">Select All</a>
			  	</th>
			{/heading_row}
			{assign var=count value=0}
			{foreach name=transactions item=transaction from=$transactions}
				{assign var=count value=$count+1}
				{assign var=rowid value='row'|cat:$count}
				<tr rel="{$rowid}">
					{grid_cell model=$transaction cell_num=1 field='transaction_date'}
							{$transaction->transaction_date}
					{/grid_cell}
					{grid_cell model=$transaction cell_num=2 field="date"}
							{$transaction->reference}
					{/grid_cell}
					{grid_cell model=$transaction cell_num=3 field='company'}
							{$transaction->company}
					{/grid_cell}
					{grid_cell model=$transaction cell_num=3 field='person'}
							{$transaction->person}
					{/grid_cell}
					{grid_cell model=$transaction cell_num=4 field='ext_reference'}
							{$transaction->ext_reference}
					{/grid_cell}
					{grid_cell model=$transaction cell_num=5 field='payment_type'}
							{$transaction->payment_type}
					{/grid_cell}
					{grid_cell model=$transaction cell_num=6 field='description'}
							{$transaction->description}
					{/grid_cell}
					{grid_cell model=$transaction cell_num=7 class='numeric' field='gross_value'}
							{$transaction->gross_value}
					{/grid_cell}
					<td align='center'>
						<input type="hidden" id="gross_value{$rowid}" value="{$transaction->gross_value}" />
						<input id='allocate{$rowid}' class="checkbox" type="checkbox" name="transactions[{$transaction->id}]" data-transaction-value="{$transaction->gross_value}" />
					</td>
				</tr>
			{/foreach}
			<tr>
				<td colspan="6">&nbsp;</td>
				<td>Selected total</td>
				<td>
					<input type='text' class="numeric" name="allocated_total" id="allocated_total" readonly="true" value="0.00">
				</td>		
			</tr>
		{/data_table}
		<p class="reconcile_error error" style="display: none;">Can not reconcile bank account, differences do not match. Difference is <span></span></p>
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}