{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper title=$entityattachments->title}
	{advanced_search}
	{paging}
	{assign var=templatemodel value=$entityattachments->getModel()}
	{assign var=fields value=$entityattachments->getHeadings()}
	{data_table}
		<thead>
			<tr>
				{foreach name=headings item=heading key=fieldname from=$fields}
					{heading_cell field=$fieldname model=$entityattachments->getModel()}
						{$heading}
					{/heading_cell}
				{/foreach}
				<th>Actions</th>
			</tr>
		</thead>
		{foreach name=datagrid item=model from=$entityattachments}
			{grid_row model=$model}
				{foreach name=gridrow item=tag key=fieldname from=$fields}
					{if $fieldname == 'file'}
					<td>
						{link_to _target="_blank" _rel="noopener noreferrer" module=$module controller="attachments" action="view_file" id=$model->id value=$model->getFormatted($fieldname)}
					</td>
					{else}
					{grid_cell field=$fieldname cell_num=$smarty.foreach.gridrow.iteration model=$model collection=$entityattachments}
						{if ($model->isEnum($fieldname))}
							{$model->getFormatted($fieldname)}
						{else}
							{$model->getFormatted($fieldname)}
						{/if}
					{/grid_cell}
					{/if}
				{/foreach}
				<td>
					{link_to module=$module controller="attachments" action="edit" id=$model->id value="Replace"}&nbsp;&nbsp;
					{assign var=dt value=['data_uz-confirm-message'=>"Delete attachment?|This cannot be undone.", 'data_uz-action-id' => $model->id]}
					{link_to _class="confirm" data_attrs=$dt module=$module controller="attachments" action="delete" id=$model->id value="Delete"}
				</td>
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
	<div style="clear: both;">&nbsp;</div>
{/content_wrapper}