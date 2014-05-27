{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$whstore}
				{view_data attribute='store_code'}
				{view_data attribute='description'}
				{view_data attribute='address' value=$model->address->address}
			{/with}
		</dl>
	</div>
	{include file="elements/datatable.tpl" collection=$whlocations}
{/content_wrapper}