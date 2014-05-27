{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{assign var=fields value=$collection->getHeadings()}
<table cellspacing="2" cellpadding="0" class="datagrid_inline" id="datagrid_inline1">
	{if $showheading}
		<thead>
			<tr>
				{foreach name=headings item=heading key=fieldname from=$fields}
					{heading_cell field=$fieldname model=$collection->getModel()}
						{$heading}
					{/heading_cell}
				{/foreach}
			</tr>
		</thead>
	{/if}
	{foreach name=datagrid item=model from=$collection}
		{grid_row model=$model}
			{foreach name=gridrow item=tag key=fieldname from=$fields}
				{grid_cell field=$fieldname model=$model collection=$collection}
					{if ($model->isEnum($fieldname))}
					    {$model->getFormatted($fieldname)}
							{else}
						{$model->getFormatted($fieldname)}
					{/if}
				{/grid_cell}
			{/foreach}
		{/grid_row}
	{/foreach}
</table>