{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$WorkSchedulePart}
			{view_data attribute="work_schedule_id"}
			{view_data attribute="productline_header_id"}
			{view_data attribute="order_qty"}
			{view_data attribute="order_id"}
			{view_data attribute="created"}
			{view_data attribute="createdby"}
			{view_data attribute="lastupdated"}
			{view_data attribute="alteredby"}
		{/with}
	</div>
{/content_wrapper}