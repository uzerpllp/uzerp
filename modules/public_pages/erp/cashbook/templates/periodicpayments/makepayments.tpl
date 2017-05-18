{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.13 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{form controller=$self.controller action="savepayments"} 
		{data_table}
			{heading_row}
				{heading_cell field="status" }
					Status
				{/heading_cell}
				{heading_cell field="source" }
					Source
				{/heading_cell}
				{heading_cell field="company" }
					Company
				{/heading_cell}
				{heading_cell field="person" }
					Person
				{/heading_cell}
				{heading_cell field="cb_account" }
					Bank Account
				{/heading_cell}
				{heading_cell field="currency" }
					Currency
				{/heading_cell}
				{heading_cell field="payment_type" }
					Payment Type
				{/heading_cell}
				{heading_cell field="frequency"}
					Frequency
				{/heading_cell}
				{heading_cell field="next_due_date"}
					Next Due Date
				{/heading_cell}
				{heading_cell field="ext_reference"}
					Description/<br>
					External Reference
				{/heading_cell}
				<th class='right'>
					Net Value
				</th>
				<th class='right'>
					Tax Value
				</th>
				<th class='right'>
					Gross Value
				</th>
				<th class='right'>
					Pay?
				</th>
				<th class='right'>
					Skip?
				</th>
			{/heading_row}
			{foreach name=datagrid item=model from=$periodicpayments}
				{grid_row model=$model}
					{assign var=id value=$model->id}
					{grid_cell model=$model cell_num=1 field="status"}
						{$model->getFormatted('status')}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="source"}
						{$model->getFormatted('source')}
					{/grid_cell}
					{grid_cell model=$model cell_num=3 field="company"}
						{$model->company}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="person"}
						{$model->person}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="cb_account"}
						{$model->cb_account}
					{/grid_cell}
					{grid_cell model=$model cell_num=5 field="currency"}
						{$model->currency}
					{/grid_cell}
					{grid_cell model=$model cell_num=6 field="payment_type"}
						{$model->payment_type}
					{/grid_cell}
					{grid_cell model=$model cell_num=7 field="frequency"}
						{$model->getFormatted('frequency')}
					{/grid_cell}
					<td width=110>
						<input type="text" class="datefield" name="PeriodicPayment[{$model->id}][next_due_date]" id="PeriodicPayment_{$model->id}_next_due_date" value="{$model->next_due_date|next_working_day}">
					</td>
					<td>
						{input model=$model type="text" attribute='description' rowid=$id number=$id tags='none' nolabel=true}
						<br>
						{input model=$model type="text" attribute='ext_reference' rowid=$id number=$id tags='none' nolabel=true}
					</td>
					<td class="numeric">
						{if $model->source=='CR' || $model->source=='CP'}
							{if $model->variable=="t"}
								{input model=$model type="text" class="numeric net_value" attribute="net_value" value="{$model->net_value}" rowid=$id number=$id tags='none' nolabel=true}
							{else}
								{$model->getFormatted('net_value')}
								<input type="hidden" name="PeriodicPayment[{$model->id}][net_value]" value="{$model->net_value}" >
							{/if}
						{/if}
					</td>
					<td class="numeric">
						{if $model->source=='CR' || $model->source=='CP'}
							{if $model->variable=="t"}
								{input model=$model type="text" class="numeric tax_value" attribute="tax_value" value="{$model->tax_value}" rowid=$id number=$id tags='none' nolabel=true}
							{else}
								{$model->getFormatted('tax_value')}
								<input type="hidden" name="PeriodicPayment[{$model->id}][tax_value]" value="{$model->tax_value}" >
							{/if}
						{/if}
					</td>
					<td class="numeric">
						{if $model->source=='SR' || $model->source=='PP'}
							{if $model->variable=="t"}
								<input class="numeric" type="text" name="PeriodicPayment[{$model->id}][gross_value]" value="{$model->gross_value}">
							{else}
								{$model->getFormatted('gross_value')}
								<input type="hidden" name="PeriodicPayment[{$model->id}][gross_value]" value="{$model->gross_value}">
							{/if}
						{elseif $model->source=='CR' || $model->source=='CP'}
							<span id="PeriodicPayment_gross_value{$model->id}">{$model->gross_value}</span>
						{/if}
					</td>
					<td>
						{input model=$model type="checkbox" class="pay" attribute="pay" rowid=$id number=$id tags='none' nolabel=true}
					</td>
					<td>
						{input model=$model type="checkbox" class="skip" attribute="skip" rowid=$id number=$id tags='none' nolabel=true}
					</td>
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
	<div id="data_grid_footer" class="clearfix">
		{paging}
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}