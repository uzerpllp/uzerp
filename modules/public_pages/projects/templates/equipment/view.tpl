{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$ProjectEquipment}
		<dl id="view_data_left">
			{view_data attribute="name"}
			{view_data attribute="setup_cost"}
			{view_data attribute="cost_rate"}
			{view_data attribute='uom_name'}
			{view_data attribute="available" label="Available"}
		</dl>
		{/with}
	</div>
{/content_wrapper}