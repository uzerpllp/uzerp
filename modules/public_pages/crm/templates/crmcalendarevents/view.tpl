{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$event}
			<dl id="view_data_left">
				<dt class="heading">Event Details</dt>
				{view_data attribute='title'}
				{view_data attribute='crm_calendar'}
				{view_data attribute='start_date'}
				{view_data attribute='end_date'}
			</dl>
		{/with}
	</div>
{/content_wrapper}