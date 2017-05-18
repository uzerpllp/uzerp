{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
<div id="data_grid_header" class="clearfix">
	{if $num_pages > 0}
		<span class="paging">
			{if $cur_page >2 }
				{link_first}
			{/if}
			{if $cur_page >1}
				{link_prev page=$cur_page}
			{/if}
			{$cur_page} of {$num_pages}
			{if $cur_page lt $num_pages}
				{link_next page=$cur_page}
			{/if}
			{if $cur_page lt ($num_pages-1)}
			{link_last}
			{/if}
		</span>
	{/if}
</div>
{assign var=templatemodel value=$collection->getModel()}
{data_table}
	{heading_row}
		{foreach name=headings item=heading key=fieldname from=$collection->getHeadings()}
			{heading_cell field=$fieldname}
				{$heading}
			{/heading_cell}
		{/foreach}
		<th></th>
	{/heading_row}
	{foreach name=datagrid item=model from=$collection}
		{grid_row model=$model}
			{foreach name=gridrow item=tag key=fieldname from=$collection->getHeadings()}
				{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$collection}
					{$model->$fieldname}
				{/grid_cell}
			{/foreach}
			<td>{link_to module=$module controller=$controller action="delete" id=$model->id value="Delete"}</td>
		{/grid_row}
	{foreachelse}
		<tr><td colspan="0">No matching records found!</td></tr>
	{/foreach}
{/data_table}
<div id="data_grid_footer" class="clearfix">
	{if $num_pages > 0}
		<span class="paging">
			{if $cur_page >2 }
				{link_first}
			{/if}
			{if $cur_page >1}
				{link_prev page=$cur_page}
			{/if}
			{$cur_page} of {$num_pages}
			{if $cur_page lt $num_pages}
				{link_next page=$cur_page}
			{/if}
			{if $cur_page lt ($num_pages-1)}
			{link_last}
			{/if}
		</span>
	</div>
<div style="clear: both;">&nbsp;</div>