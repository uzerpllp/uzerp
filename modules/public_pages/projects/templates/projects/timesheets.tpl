{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{assign var=collection value=$hours}
	{data_table}
		<thead>
			<tr>
				{foreach name=headings item=heading key=fieldname from=$fields}
					{heading_cell field=$heading model=$collection->getModel()}
						{$heading}
					{/heading_cell}
				{/foreach}
			</tr>
		</thead>
		{foreach name=datagrid item=model from=$collection}
			{grid_row model=$model}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection no_escape=true start_date='' end_date='' }
						{if $tag=='project'}
							{assign var=project_id value=$model->project_id}
							{assign var=value value=$model->$tag}
							{link_to module=$module controller=$controller action=view id=$project_id value=$value}
						{else}
							{$model->$tag}
						{/if}
					{/grid_cell}
				{/foreach}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">
					No matching records found!
				</td>
			</tr>
		{/foreach}
	{/data_table}
{/content_wrapper}