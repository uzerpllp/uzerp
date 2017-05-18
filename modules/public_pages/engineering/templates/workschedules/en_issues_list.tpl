{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper title=$type_text|cat:'s against this works schedule' flash=false}
	{assign var=fields value=$collection->getHeadings()}
	{data_table}
		<thead>
			<tr>
				{foreach name=headings item=heading key=fieldname from=$fields}
					{heading_cell field=$fieldname model=$collection->getModel()}
						{$heading}
					{/heading_cell}
				{/foreach}
				<th>&nbsp;</th>
			</tr>
		</thead>
		{foreach name=datagrid item=model from=$collection}
			{grid_row model=$model}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
						{if ($model->isEnum($fieldname))}
							{$model->getFormatted($fieldname)}
						{else}
							{$model->getFormatted($fieldname)}
						{/if}
					{/grid_cell}
				{/foreach}
				<td></td>
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
{/content_wrapper}