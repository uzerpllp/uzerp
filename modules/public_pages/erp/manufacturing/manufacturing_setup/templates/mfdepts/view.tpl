{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$transaction}
				{view_data attribute="dept_code"}
				{view_data attribute="dept"}
				{view_data attribute="production_recording"}
			{/with}
		</dl>
	</div>
	<div id="view_page" class="clearfix">
		{paging}
		{view_section heading="centres"}
		{data_table}
			{heading_row}
				{heading_cell field="work_centre"}
					Work Centre
				{/heading_cell}
				{heading_cell field="centre" }
					Centre Name
				{/heading_cell}
				{heading_cell field="centre_rate" }
					Centre Rate
				{/heading_cell}
				{heading_cell field="production_recording" }
					Production Recording
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$mfcentres}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=1 field="work_centre"}
						{$model->work_centre}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="centre"}
						{$model->centre}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="centre_rate"}
						{$model->centre_rate}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="production_recording"}
						{$model->production_recording}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{paging}
		{/view_section}
	</div>
{/content_wrapper}