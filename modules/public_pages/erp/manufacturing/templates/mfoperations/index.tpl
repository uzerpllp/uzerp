{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction attribute="item_code"}
			{view_data model=$transaction attribute="description"}
			{view_data model=$transaction attribute="uom_name"}
			{view_data model=$transaction attribute="comp_class"}
			{view_data model=$transaction attribute="type_code"}
			{view_data model=$transaction attribute="product_group"}
		</dl>
	</div>
	{include file="elements/datatable.tpl" collection=$mfoperations}
{/content_wrapper}