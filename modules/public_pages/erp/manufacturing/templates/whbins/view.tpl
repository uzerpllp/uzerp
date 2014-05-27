{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction label="Store" value=$whstore}
			{view_data model=$transaction label="Location" attribute="whlocation"}
			{view_data model=$transaction attribute="bin_code"}
			{view_data model=$transaction attribute="description"}
		</dl>
	</div>
{/content_wrapper}