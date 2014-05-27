{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{foreach name=datagrid item=model from=$collection}
	{grid_row model=$model}
		{foreach name=gridrow item=tag key=fieldname from=$fields}
			{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
				{$model->getFormatted($fieldname)}
			{/grid_cell}
		{/foreach}
		{if $allow_delete}
			<td>
				{include file='elements/delete_row.tpl'}
			</td>
		{/if}
	{/grid_row}
{foreachelse}
	<tr>
		<td colspan="0">No matching records found!</td>
	</tr>
{/foreach}