{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper title=$related_collection->title}
	<div id="view_page" class="clearfix">
	    <dl class="float-left" >
			{with model=$SOProductlineHeader legend="SOProduct Details"}
				{view_data attribute='description'}
				{view_data attribute='stitem' label='Stock Item'}
				{view_data attribute='product_group'}
				{view_data attribute='uom_name'}
			{/with}
		</dl>
	</div>
	{include file="elements/datatable.tpl" collection=$related_collection}
{/content_wrapper}