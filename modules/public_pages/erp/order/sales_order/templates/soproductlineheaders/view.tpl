{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$SOProductlineHeader}
				{view_data attribute="stitem" label="Stock Item"}
				{view_data attribute="product_group"}
				{view_data attribute="uom_name"}
				{view_data attribute="gl_account"}
				{view_data attribute="gl_centre"}
				{view_data attribute="description"}
				{view_data attribute="start_date"}
				{view_data attribute="end_date"}
				{view_data attribute="tax_rate"}
				{view_data attribute="not_despatchable" label="Hidden on Despatch Notes"}
			{/with}
			{with model=$SOProductlineHeader->item_detail}
				{view_data attribute="latest_cost"}
				{view_data attribute="std_cost"}
			{/with}
		</dl>
	</div>
{/content_wrapper}