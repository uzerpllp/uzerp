{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
		{data_table}
			{heading_row}
				{heading_cell field='type'}
					Type
				{/heading_cell}
				{heading_cell field='created'}
					Created
				{/heading_cell}
				{heading_cell field='Requested By'}
					Requested By
				{/heading_cell}
				{heading_cell field='process'}&nbsp{/heading_cell}
				{heading_cell field='cancel'}&nbsp{/heading_cell}
				{heading_cell field='details'}&nbsp{/heading_cell}
				{heading_cell field='counts'}
					Counts
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$outputheaders}
				{assign var=id value=$model->id}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=2 field='type'}
						{$model->type}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field='created'}
						{$model->created|un_fix_date}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field='createdby'}
						{$model->createdby}
					{/grid_cell}
					<td align=left>
						{if $model->processed=='t'}
							Completed
						{else}
							{link_to module=$module controller=$controller action='process_output' id=$model->id type='process' value='Process'}
						{/if}
					</td>
					<td align=left>
						{if  $model->processed=='f'}
							{link_to module=$module controller=$controller action='process_output' id=$model->id type='cancel' value='Cancel'}
						{/if}
					</td>
					<td align=left>
						{link_to module=$module controller=$controller action='output_detail' id=$model->id value='Details'}
					</td>
					<td>
						{foreach key=key item=count from=$model->detail_counts()}
							{$key} {$count} <br>
						{/foreach}
					</td>
				{/grid_row}
			{/foreach}
		{/data_table}
	{paging}
{/content_wrapper}