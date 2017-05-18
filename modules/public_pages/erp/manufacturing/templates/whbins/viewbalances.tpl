{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$transaction}
				{view_data model=$transaction label="Store" value=$whstore}
				{view_data attribute='whlocation' label='location'}
				{view_data attribute='bin_code'}
				{view_data attribute='description'}
			{/with}
		</dl>
	</div>
	{include file='elements/datatable.tpl' collection=$stbalances}
{/content_wrapper}