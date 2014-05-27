{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper title=$related_collection->title}
	{advanced_search}
	{assign var=templatemodel value=$collection->getModel()}
	{assign var=fields value=$collection->getHeadings()}
	{form controller=$controller action="save_contras" _id=$smarty.get.id}
		{paging}
		{input type='hidden' model=$ledger_account attribute='id'}
		{input type='hidden' model=$ledger_account attribute='payment_term_id'}
		{input type='hidden' model=$ledger_account attribute='company_id'}
		{input type='hidden' model=$ledger_account attribute='currency_id'}
		{input type='hidden' model=$ledger_account attribute='rate' value=$ledger_account->currency_detail->rate}
		{data_table}
			<thead>
				<tr>
					{with model=$collection->getModel()}
						{foreach name=headings item=heading key=fieldname from=$fields}
							{heading_cell field=$fieldname}
								{$heading}
							{/heading_cell}
						{/foreach}
					{/with}
					<th>
						{if $can_contra}
							Contra?
						{/if}
					</th>
				</tr>
			</thead>
			{foreach name=datagrid item=model from=$collection}
			{grid_row model=$model}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{if $fieldname=='status' && ($model->$fieldname==$model->paid() || $model->$fieldname==$model->partPaid())}
						{grid_cell no_escape=true field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
							{link_to module=$module controller=$clickcontroller action="view_allocations" trans_id=$model->id value=$model->getFormatted($fieldname)}
						{/grid_cell}
					{elseif $fieldname=='our_reference'}
						{grid_cell no_escape=true field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
							{if $model->transaction_type==$model->invoice() || $model->transaction_type==$model->creditNote()}
								{link_to module=$invoice_module controller=$invoice_controller action="view" invoice_number=$model->our_reference value=$model->getFormatted($fieldname)}
							{elseif $model->transaction_type==$model->receipt() || $model->transaction_type==$model->payment()}
								{link_to module=cashbook controller=cbtransactions action="view" reference=$model->our_reference value=$model->getFormatted($fieldname)}
							{else}
								{$model->getFormatted($fieldname)}
							{/if}
						{/grid_cell}
					{else}
						{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
							{if ($model->isEnum($fieldname))}
								{$model->getFormatted($fieldname)}
							{else}
								{$model->getFormatted($fieldname)}
							{/if}
						{/grid_cell}
					{/if}
				{/foreach}
				<td>
					{if $can_contra}
						{input type='hidden' model=$model attribute='os_value' rowid=$model->id number=$model->id}
						{assign var=id value=$model->id}
						{if ($page_data.$id.contra)}
							{input type='checkbox' class='checkbox contra' model=$model attribute='contra' rowid=$id number=$id tags=none label='' value=true}
						{else}
							{input type='checkbox' class='checkbox contra' model=$model attribute='contra' rowid=$id number=$id tags=none label=''}
						{/if}
					{/if}
				</td>
			{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
			{if $can_contra}
				<tr>
					<td align='right' colspan=4>
						Contra total
					</td>
					<td align='right'>
						<input type='text' class="numeric" name="contra_total" id="contra_total" value={$contra_total|string_format:"%.2f"} readonly>
					</td>
					<td align='right' colspan=8>
					</td>
				</tr>
			{/if}
		{/data_table}
		{if $can_contra}
			{submit}
		{/if}
		{paging}
	{/form}
	<div style="clear: both;">&nbsp;</div>
{/content_wrapper}