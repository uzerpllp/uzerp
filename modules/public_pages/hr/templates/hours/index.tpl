{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{assign var=collection value=$hours}
Special HR Hours Index
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
					{grid_cell field=$fieldname model=$model collection=$collection no_escape=true start_date=$start_date end_date=$end_date }
						{assign var=value value=$model->$tag}
						{link_to module=$module controller=$controller action=view person_id=$model->id start_date="$start_date" end_date="$end_date" value=$value}
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