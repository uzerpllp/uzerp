{** 
 *	(c) 2018 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{advanced_search}
	{paging}
		{form controller="poplanned" action="createorder"}
		{assign var=fields value=$plannedorders->getHeadings()}
		{data_table}
			<thead>
				{heading_row}
					{foreach name=headings item=heading key=fieldname from=$fields}
						{heading_cell field=$fieldname model=$plannedorders->getModel()}
							{$heading}
						{/heading_cell}
					{/foreach}
					<th>
						Select for Order
					</th>
				{/heading_row}
			</thead>
			<tr>
				<td colspan="7"></td><td><input type="checkbox" name="select-all" id="select-all" class="checkbox"> Select All</td>
			</tr>
			{foreach name=datagrid item=model from=$plannedorders}
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
					<td>
						<input type="checkbox" name="update[{$model->id}]" id="update{$model->id}" class="checkbox select" {if $smarty.session.form_data[$model->id] == 'on'}checked{/if}/>
					</td>
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{submit value="Create Order" tags="none"}
	{/form}
	{*{paging}*}
	<div id="data_grid_footer" class="clearfix">
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}