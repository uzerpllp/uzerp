{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{assign var=fields value=$systemobjectpolicys->getHeadings()}
	{data_table}
		{heading_row}
			{foreach name=headings item=heading key=fieldname from=$fields}
				{if $fieldname!='is_id_field'}
					{heading_cell field=$fieldname model=$systemobjectpolicys->getModel()}
						{$heading}
					{/heading_cell}
				{/if}
			{/foreach}
		{/heading_row}
		{foreach name=datagrid item=model from=$systemobjectpolicys}
			{grid_row model=$model}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{if $fieldname!='is_id_field'}
						{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
							{if ($fieldname=='fieldname')}
								{$model->get_field()}
							{elseif ($fieldname=='value')}
								{$model->getvalue()}
							{elseif ($fieldname=='module_component')}
								{$model->getComponentTitle()}
							{elseif ($model->isEnum($fieldname))}
								{$model->getFormatted($fieldname)}
							{else}
								{$model->getFormatted($fieldname)}
							{/if}
						{/grid_cell}
					{/if}
				{/foreach}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
{/content_wrapper}