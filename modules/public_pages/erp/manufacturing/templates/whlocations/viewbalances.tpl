{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.14 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$transaction}
				{view_data label="Store" attribute="whstore"}
				{view_data attribute='location'}
				{view_data attribute='description'}
			{/with}
		</dl>
	</div>
	{include file='elements/datatable.tpl' collection=$stbalances}
{/content_wrapper}