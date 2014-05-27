{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{if isset($datadef)}
				{view_data model=$datadef attribute="external_system"}
				{view_data model=$datadef attribute="name"}
			{/if}
		</dl>
		<div id="view_data_bottom">
			{paging}
			{data_table}
				{heading_row}
					{if !isset($datadef)}
						{heading_cell field="external_system"}
							External System
						{/heading_cell}
						{heading_cell field="data_definition"}
							Data Definition
						{/heading_cell}
					{/if}
					{heading_cell field="created"}
						Date
					{/heading_cell}
					{heading_cell field="name"}
						Name
					{/heading_cell}
					{heading_cell field="status"}
						Status
					{/heading_cell}
					{heading_cell field="message"}
						Message
					{/heading_cell}
					<th>
					</th>
					{heading_cell field="identifier_field"}
						Identifier
					{/heading_cell}
					{heading_cell field="identifier_value"}
						Ref.
					{/heading_cell}
				{/heading_row}
				{foreach name=datagrid item=model from=$editransactionlogs}
					{grid_row model=$model}
						{if !isset($datadef)}
							{grid_cell model=$model cell_num=2 field="external_system"}
								{$model->external_system}
							{/grid_cell}
							{grid_cell model=$model cell_num=2 field="data_definition"}
								{$model->data_definition}
							{/grid_cell}
						{/if}
						{grid_cell model=$model cell_num=2 field="created"}
							{$model->created}
						{/grid_cell}
						{grid_cell model=$model cell_num=2 field="name" no_escape=true}
							{link_to module=$module controller=datadefinitions action='view_file' id=$model->data_definition_id filename=$model->name implements=$model->data_definition_data->implementation_class value=$model->name}
						{/grid_cell}
						{grid_cell model=$model cell_num=2 field="status"}
							{$model->getFormatted('status')}
						{/grid_cell}
						{grid_cell model=$model cell_num=2 field="message"}
							{$model->message}
						{/grid_cell}
						<td>
							{if $model->status=='F'}
								{link_to module=$module controller=datadefinitions action='process_file' id=$model->data_definition_id data=$id filename=$model->name retry='true' value='Retry' implements=$model->data_definition_data->implementation_class}
							{/if}
						</td>
						{grid_cell model=$model cell_num=2 field="identifier_field"}
							{$model->identifier_field|prettify}
						{/grid_cell}
						<td>
							{$model->identifier_value}
						</td>
					{/grid_row}
				{foreachelse}
					<tr>
						<td colspan="0">No matching records found!</td>
					</tr>
				{/foreach}
			{/data_table}
			{paging}
		</div>
	</div>
{/content_wrapper}