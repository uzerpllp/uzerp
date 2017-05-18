{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{advanced_search}
	{assign var=templatemodel value=$collection->getModel()}
	{form controller=$self.controller action=$form_action notags=true}
		{paging}
		{assign var=fields value=$collection->getHeadings()}
		{data_table class="paging-select"}
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
				{assign var=id value=$model->id}
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
					<td align=left>
						{if $selected.$id.select=='on'}
							{input value="true" model=$model type="checkbox" attribute="select" rowid="$id" number="$id" tags='none' nolabel=true }
						{else}
							{input model=$model type="checkbox" attribute="select" rowid="$id" number="$id" tags='none' nolabel=true}
						{/if}
					</td>
					{input  model=$model type='hidden' attribute="description" rowid="$id" number="$id" value=$description}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		<input type="hidden" id="link" value="{$link}">
		<input type='hidden' name='session_data_key' value={$session_data_key}>
		Select count <input type='text' readonly id=selected_count name=selected_count value="{$count_selected}" class='numeric'>
		{submit tags='none' name='save' value=$submit_text}
	{/form}
	{form controller=$self.controller action=$action notags=true}
		<input type='hidden' name='session_data_key' value={$session_data_key}>
		{submit tags='none' name='select_all' value=$select_all_text}
	{/form}
	<div style="clear: both;">
	</div>
{/content_wrapper}