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
			{view_data model=$transaction attribute="description"}
			{view_data model=$transaction label='Cost' attribute="latest_osc"}
		</dl>
	</div>
{/content_wrapper}