{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction label='Stock Item' attribute="stitem"}
			{view_data model=$transaction attribute="op_no"}
			{view_data model=$transaction attribute="start_date"}
			{view_data model=$transaction attribute="end_date"}
			{view_data model=$transaction attribute="remarks"}
			{view_data model=$transaction attribute="volume_target"}
			{view_data model=$transaction label="UOM" attribute="volume_uom"}
			{view_data model=$transaction attribute="volume_period"}
			{view_data model=$transaction label="Quality Target(%)" attribute="quality_target"}
			{view_data model=$transaction label="Uptime Target(%)" attribute="uptime_target"}
			{view_data model=$transaction label='Resource' attribute="mfresource"}
			{view_data model=$transaction attribute="resource_qty"}
			{view_data model=$transaction label='Work Centre' attribute="mfcentre"}
		</dl>
	</div>
{/content_wrapper}