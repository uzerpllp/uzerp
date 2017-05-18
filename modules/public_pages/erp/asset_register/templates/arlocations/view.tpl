{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$ARLocation}
			{view_data attribute="description"}
			{view_data attribute="pl_glcentre" label='P&L Cost Centre'}
			{view_data attribute="bal_glcentre" label='Balance Sheet Cost Centre'}
		{/with}
	</div>
{/content_wrapper}