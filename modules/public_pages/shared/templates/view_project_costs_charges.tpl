{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper title=$projectcostcharges->title}
	{paging}
	{assign var=templatemodel value=$projectcostcharges->getModel()}
	{assign var=fields value=$projectcostcharges->getHeadings()}
	{data_table}
		{heading_row}
			{foreach name=headings item=heading key=fieldname from=$fields}
				{heading_cell field=$fieldname model=$templatemodel}
					{$heading}
				{/heading_cell}
			{/foreach}
		{/heading_row}
		{foreach name=datagrid item=model from=$projectcostcharges}
			{grid_row model=$model}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model}
						{if ($model->isEnum($fieldname))}
							{$model->getFormatted($fieldname)}
       	    	    	{else}
							{$model->getFormatted($fieldname)}
						{/if}
					{/grid_cell}
				{/foreach}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	
	{/data_table}
	{paging}
{/content_wrapper}