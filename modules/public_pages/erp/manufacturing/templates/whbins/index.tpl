{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.11 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$whlocation}
				{view_data attribute='whstore' label='Store'}
				{view_data attribute='location'}
				{view_data attribute='description'}
				{view_data attribute='has_balance'}
				{view_data attribute='supply_demand'}
				{view_data attribute='bin_controlled'}
				{view_data attribute='saleable'}
				{view_data attribute='pickable'}
				{view_data attribute='glaccount' label='GL Account'}
				{view_data attribute='glcentre' label='GL Centre'}
			{/with}
		</dl>
	</div>
	{include file="elements/datatable.tpl" collection=$whbins}
{/content_wrapper}