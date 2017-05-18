{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Holidayentitlement}
			<dl class="float-left">
				<dt class="heading">Holiday Entitlement Details</dt>
				{view_data attribute='employee' label='Name'}
				{view_data attribute='num_days'}
				{view_data attribute='start_date'}
				{view_data attribute='end_date'}
				{view_data attribute='statutory_days'}
			</dl>
		{/with}
	</div>
{/content_wrapper}