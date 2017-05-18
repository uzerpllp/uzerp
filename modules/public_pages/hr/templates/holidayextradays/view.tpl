{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Holidayextraday}
		<dl class="float-left">
			<dt class="heading">Holiday Extra Details</dt>
				{view_data attribute='employee' label='Employee Name'}
				{view_data attribute='num_days' label='Number of Days'}
				{view_data attribute='reason'}
				{view_data attribute='authorisedby' label='Authorised By'}
				{view_data attribute='created'}		
			</dl>
		{/with}
	</div>
{/content_wrapper}