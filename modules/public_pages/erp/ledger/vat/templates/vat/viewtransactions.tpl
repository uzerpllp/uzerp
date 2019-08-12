{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{if $box == '4'}
	{assign var=data value=$vatinputss}
{else if $box == '6'}
	{assign var=data value=$vatoutputss}
{else if $box == '8'}
	{assign var=data value=$vateusaless}
{else if $box == '9'}
	{assign var=data value=$vateupurchasess}
{/if}
{content_wrapper}
	{paging}
	{data_table}
		{heading_row}
			<th>
				Date
			</th>
			{heading_cell field="docref"}
				Doc Ref:
			{/heading_cell}
			<th>
				Ext Ref.
			</th>
			<th>
				Company
			</th>
			{heading_cell field="comment"}
				Comment
			{/heading_cell}
			{heading_cell field="vat" class="right"}
				Vat
			{/heading_cell}
			{heading_cell field="net" class="right"}
				Net
			{/heading_cell}
			{heading_cell field="source"}
				Source
			{/heading_cell}
			{heading_cell field="type"}
				Type
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$data}
			{assign var=totalVat value=$totalVat+$model->vat}
			{assign var=totalNet value=$totalNet+$model->net}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="transaction_date" no_escape=true}
					{link_to module='general_ledger' controller='gltransactions' action='view' id=$model->gl_id value=$model->transaction_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="docref" no_escape=true}
					{if $model->source=='C'}
						{link_to module='cashbook' controller='cbtransactions' action='view' reference=$model->docref value=$model->docref}
					{elseif $model->source=='P'}
						{if $model->type=='J' || $model->type=='P'}
							{link_to module=purchase_ledger controller=pltransactions action='view' our_reference=$model->docref value=$model->docref}
						{else}
							{link_to module=purchase_invoicing controller=pinvoices action='view' invoice_number=$model->docref value=$model->docref}
						{/if}
					{elseif $model->source=='S'}
						{if $model->type=='J' || $model->type=='P'}
							{link_to module=sales_ledger controller=sltransactions action='view' our_reference=$model->docref value=$model->docref}
						{else}
							{link_to module=sales_invoicing controller=sinvoices action='view' invoice_number=$model->docref value=$model->docref}
						{/if}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="type"}
					{$model->ext_reference}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="type"}
					{if $model->supplier}
						{$model->supplier}
					{else if $model->customer}
						{$model->customer}
					{/if}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="comment"}
					{$model->comment}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="vat" class="numeric"}
					{$model->vat|string_format:"%.2f"}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="net" class="numeric"}
					{$model->net|string_format:"%.2f"}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="source"}
					{$model->getFormatted('source')}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="type"}
					{$model->getFormatted('type')}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}

	{/data_table}
	<div id="data_grid_footer" class="clearfix">
		{paging}
	</div>
{/content_wrapper}