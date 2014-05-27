{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{advanced_search}
	<div id="view_page" class="clearfix">
		{with model=$outputheader}
			<dl id="view_data_left">
				{view_data attribute='type'}
				{view_data attribute='filename'}
				{view_data attribute='printtype' label='Format'}
				{view_data attribute='printer'}
				{if $model->printtype=='csv'}
					{view_data attribute='fieldnames'}
					{view_data attribute='fieldseparater'}
					{view_data attribute='textdelimiter'}
				{/if}
				{view_data attribute='processed'}
			</dl>
			<dl id="view_data_right">
				{view_data attribute='emailtext'}
			</dl>
		{/with}
	</div>
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field='description'}
				Description
			{/heading_cell}
			{heading_cell field='printaction'}
				Action
			{/heading_cell}
			{heading_cell field='email'}
				Email
			{/heading_cell}
			{heading_cell field='email'}
				Status
			{/heading_cell}
			<th></th>
		{/heading_row}
		{foreach name=datagrid item=model from=$outputdetails}
			{grid_row model=$model}
				{grid_cell model=$model field='description'}
					{$model->description}
				{/grid_cell}
				{grid_cell model=$model field='printaction'}
					{$model->printaction}
				{/grid_cell}
				{grid_cell model=$model field='email'}
					{$model->email}
				{/grid_cell}
				{grid_cell model=$model field='status'}
					{$model->status}
				{/grid_cell}
				<td>
					{link_to module=$module controller=$controller action=view id=$model->select_id value='detail'}
				</td>
			{/grid_row}
		{/foreach}
	{/data_table}
	{paging}
{/content_wrapper}