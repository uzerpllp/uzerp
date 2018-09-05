{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$SOProductlineHeader legend="SOProduct Details"}
		    <dl class="float-left" >
				{view_data attribute='description' link_to='"module":"'|cat:$linkmodule|cat:'","controller":"'|cat:$linkcontroller|cat:'","action":"view","id":"'|cat:$SOProductlineHeader->id|cat:'"'}
				{view_data attribute='stitem' label='Stock Item'}
				{view_data attribute='ean' label="EAN"}
				{view_data attribute='product_group'}
				{view_data attribute='uom_name'}
				{view_data attribute="tax_rate"}
				{view_data attribute='commodity_code'}
			</dl>
		    <dl class="float-right" >
				{view_data attribute="gl_account"}
				{view_data attribute="gl_centre"}
				{view_data attribute="start_date"}
				{view_data attribute="end_date"}
				{with model=$model->item_detail}
					{view_data attribute="latest_cost"}
					{view_data attribute="std_cost"}
				{/with}
			</dl>
		{/with}
	</div>
	{assign "printaction" ""}
	{include file="elements/datatable.tpl" collection=$soproductlines}
{/content_wrapper}