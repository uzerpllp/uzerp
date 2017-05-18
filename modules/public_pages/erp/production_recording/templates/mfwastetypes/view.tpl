{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.3 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$model attribute="description"}
			{view_data model=$model attribute="uom_name"}
			{view_data model=$model attribute="cost"}
		</dl>
		{if $model->mf_centres->count()>0}
			<dl id="view_data_bottom">
			{data_table}
				{heading_row}
					{heading_cell}
						Assigned Work Centres
					{/heading_cell}
				{/heading_row}
				{foreach name=mf_centres item=mf_centre from=$model->mf_centres}
					{grid_row model=$centre}
						{grid_cell model=$mf_centre cell_num=2 field="work_centre"}
							{$mf_centre->mf_centre}
						{/grid_cell}
					{/grid_row}
				{/foreach}
			{/data_table}
			</dl>
		{/if}
	</div>
{/content_wrapper}