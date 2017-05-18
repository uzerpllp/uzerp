{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$model->shift_detail attribute="shift"}
			{view_data model=$model->shift_detail attribute="shift_date"}
			{view_data model=$model->shift_detail attribute="mf_dept"}
			{view_data model=$model->shift_detail attribute="mf_centre"}
			{view_data model=$model attribute="stitem"}
			{view_data model=$model attribute="uom_name"}
		</dl>
		<dl id="view_data_right">
			{view_data model=$model attribute="wo_number"}
			{view_data model=$model attribute="output"}
			{view_data model=$model attribute="planned_time"}
			{view_data model=$model attribute="run_time_speed"}
			{view_data model=$model attribute="operators"}
		</dl>
	</div>
	{include file="elements/datatable.tpl" collection=$mfshiftwastes}
{/content_wrapper}