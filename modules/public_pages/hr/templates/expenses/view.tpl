{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Expense}
			{assign var=awaitingauth value=$model->awaitingAuthorisation()}
			{assign var=auth value=$model->authorised()}
			<dl class="float-left">
				{view_section heading="Summary" expand='open'}
					{view_data attribute='employee'}
					{view_data attribute='expense_date' label='Date'}
					{view_data attribute='expense_number' label='Expense No.'}
					{view_data attribute='our_reference' label='Reference'}
					{view_data attribute='status'}
					{view_data attribute='project'}
					{view_data attribute='task'}
				{/view_section}
			</dl>
			<dl class="float-right">
				{view_section heading="Value" expand='open'}
					{view_data attribute='net_value'}
					{view_data attribute='tax_value'}
					{view_data attribute='gross_value'}
				{/view_section}
				{view_section heading="Authorisation" expand='closed'}
					{view_data attribute='authorised_date'}
					{view_data attribute='authorised_by' value=$model->authorisor->employee}
				{/view_section}
				{view_section heading="Created/Updated" expand='closed'}
					{view_data attribute='created'}
					{view_data attribute='createdby'}
					{view_data attribute='lastupdated'}
					{view_data attribute='alteredby'}
				{/view_section}
				{view_section heading="description" expand='open'}
					{view_data attribute='description' label_position='above' label=""}
				{/view_section}
			</dl>
		{/with}
		{view_section heading="Expenses Detail" expand='open'}
			<div id="view_data_bottom">
				<table id="expenses_lines">
					<thead>
						<tr>
							<th>Line #</th>
							<th>Description</th>
							<th>Account</th>
							<th>Centre</th>
							<th class="right">Price</th>
							<th class="right">Qty</th>
							<th>Tax Rate</th>
							<th class="right">Net Value</th>
							<th class="right">Tax Value</th>
							<th class="right">Gross Value</th>
						</tr>
					</thead>
					{foreach name=lines item=line from=$Expense->lines}
						<tr data-line-number="{$line_number}" class="gridrow" >
							{if $awaitingauth || $auth}
								{assign var=line_number value=$line->line_number}
								{assign var=id value=$line->id}
								<td align='right' class="edit-line">
									{link_to module="hr" controller="expenselines" action="edit" id="$id" value="$line_number"}
								</td>
							{else}
								<td align='right'>{$line->line_number}</td>
							{/if}
							<td>{$line->item_description}</td>
							<td>{$line->glaccount}</td>
							<td>{$line->glcentre}</td>
							<td class="numeric">{$line->purchase_price|string_format:'%.4f'}</td>
							<td class="numeric">{$line->qty}</td>
							<td class="numeric">{$line->tax_rate}</td>
							<td class="numeric">{$line->net_value|string_format:'%.2f'}</td>
							<td class="numeric">{$line->tax_value|string_format:'%.2f'}</td>
							<td class="numeric">{$line->gross_value|string_format:'%.2f'}</td>
						</tr>
					{/foreach}
				</table>
			</div>
		{/view_section}
	</div>
{/content_wrapper}